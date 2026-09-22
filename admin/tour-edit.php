<?php
ob_start();
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

// Ensure all needed columns exist (safe on any MySQL/MariaDB version)
$alterCols = [
    "expert_guides TINYINT(1) NOT NULL DEFAULT 1",
    "fully_insured TINYINT(1) NOT NULL DEFAULT 1",
    "flexible_cancellation TINYINT(1) NOT NULL DEFAULT 1",
    "features TEXT DEFAULT NULL",
    "service_notes TEXT DEFAULT NULL",
    "optional_addons TEXT DEFAULT NULL",
];
foreach ($alterCols as $colDef) {
    $colName = explode(' ', $colDef)[0];
    try {
        $check = $pdo->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tours' AND COLUMN_NAME = ?");
        $check->execute([$colName]);
        if ($check->fetchColumn() == 0) {
            $pdo->exec("ALTER TABLE tours ADD COLUMN $colDef");
        }
    } catch (PDOException $e) { /* ignore */ }
}

$destinations  = getDestinations();
$countries     = getCountries();
$allCategories = getTourCategories();
$tour          = null;
$errors        = [];

$defaultFeatures = [
    ['icon' => 'fa-star',       'label' => 'Expert-led Guided Tours',  'enabled' => true],
    ['icon' => 'fa-shield-alt', 'label' => 'Fully Insured & Licensed', 'enabled' => true],
    ['icon' => 'fa-undo',       'label' => 'Flexible Cancellation',    'enabled' => true],
];

if (!empty($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM tours WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $tour = $stmt->fetch();
}

$formFeatures = $defaultFeatures;
if ($tour) {
    if (!empty($tour['features'])) {
        $parsed = json_decode($tour['features'], true);
        if (is_array($parsed) && !empty($parsed)) {
            $formFeatures = $parsed;
        }
    } else {
        $formFeatures[0]['enabled'] = (bool)($tour['expert_guides'] ?? 1);
        $formFeatures[1]['enabled'] = (bool)($tour['fully_insured'] ?? 1);
        $formFeatures[2]['enabled'] = (bool)($tour['flexible_cancellation'] ?? 1);
    }
}

$tourCategoryIds = [];
if ($tour) {
    $tcats = getTourCategoriesForTour($tour['id']);
    $tourCategoryIds = array_column($tcats, 'id');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $submittedFeatures = [];
    $icons   = $_POST['feature_icon']    ?? [];
    $labels  = $_POST['feature_label']   ?? [];
    $enabled = $_POST['feature_enabled'] ?? [];
    foreach ($labels as $i => $lbl) {
        $lbl = trim($lbl);
        if ($lbl === '') continue;
        $submittedFeatures[] = [
            'icon'    => trim($icons[$i] ?? 'fa-check'),
            'label'   => $lbl,
            'enabled' => isset($enabled[$i]),
        ];
    }
    if (empty($submittedFeatures)) $submittedFeatures = $defaultFeatures;

    $data = [
        'title'                 => trim($_POST['title'] ?? ''),
        'slug'                  => slugify(trim($_POST['slug'] ?? $_POST['title'] ?? '')),
        'destination_id'        => $_POST['destination_id'] ?: null,
        'country_id'            => $_POST['country_id'] ?: null,
        'duration'              => trim($_POST['duration'] ?? ''),
        'price'                 => $_POST['price'] ? floatval($_POST['price']) : null,
        'price_note'            => trim($_POST['price_note'] ?? ''),
        'max_group_size'        => $_POST['max_group_size'] ? intval($_POST['max_group_size']) : null,
        'description'           => $_POST['description'] ?? '',
        'itinerary'             => $_POST['itinerary'] ?? '',
        'inclusions'            => $_POST['inclusions'] ?? '',
        'exclusions'            => $_POST['exclusions'] ?? '',
        'highlights'            => $_POST['highlights'] ?? '',
        'service_notes'         => $_POST['service_notes'] ?? '',
        'optional_addons'       => $_POST['optional_addons'] ?? '',
        'features'              => json_encode($submittedFeatures),
        'expert_guides'         => 1,
        'fully_insured'         => 1,
        'flexible_cancellation' => 1,
        'featured'              => $_POST['featured'] ?? 'no',
        'status'                => $_POST['status']   ?? 'draft',
        'meta_title'            => trim($_POST['meta_title'] ?? ''),
        'meta_description'      => trim($_POST['meta_description'] ?? ''),
    ];

    // Handle images
    $images = [];
    if (!empty($_POST['existing_images'])) {
        $images = array_filter(explode("\n", str_replace("\r", '', $_POST['existing_images'])));
    }
    if (!empty($_FILES['tour_images'])) {
        foreach ($_FILES['tour_images']['tmp_name'] as $i => $tmpName) {
            if ($tmpName && $_FILES['tour_images']['error'][$i] === UPLOAD_ERR_OK) {
                $file = [
                    'tmp_name' => $tmpName,
                    'name'     => $_FILES['tour_images']['name'][$i],
                    'size'     => $_FILES['tour_images']['size'][$i],
                ];
                $result = uploadImage($file, 'tours');
                if (!empty($result['success'])) $images[] = '/' . $result['path'];
            }
        }
    }
    $data['images'] = json_encode(array_values(array_filter($images)));

    if (empty($data['title'])) $errors[] = 'Title is required';

    if (empty($errors)) {
        try {
            if ($tour) {
                $sql = "UPDATE tours SET title=:title, slug=:slug, destination_id=:destination_id, country_id=:country_id,
                        duration=:duration, price=:price, price_note=:price_note, max_group_size=:max_group_size,
                        description=:description, itinerary=:itinerary, inclusions=:inclusions, exclusions=:exclusions,
                        highlights=:highlights, service_notes=:service_notes, optional_addons=:optional_addons,
                        images=:images, features=:features,
                        expert_guides=:expert_guides, fully_insured=:fully_insured, flexible_cancellation=:flexible_cancellation,
                        featured=:featured, status=:status, meta_title=:meta_title, meta_description=:meta_description
                        WHERE id=:id";
                $data['id'] = $tour['id'];
            } else {
                $sql = "INSERT INTO tours (title, slug, destination_id, country_id, duration, price, price_note,
                        max_group_size, description, itinerary, inclusions, exclusions, highlights, service_notes, optional_addons,
                        images, features, expert_guides, fully_insured, flexible_cancellation, featured, status, meta_title, meta_description)
                        VALUES (:title, :slug, :destination_id, :country_id, :duration, :price, :price_note,
                        :max_group_size, :description, :itinerary, :inclusions, :exclusions, :highlights, :service_notes, :optional_addons,
                        :images, :features, :expert_guides, :fully_insured, :flexible_cancellation, :featured, :status, :meta_title, :meta_description)";
            }
            $stmt = $pdo->prepare($sql);
            $stmt->execute($data);
            $tourId = $tour ? $tour['id'] : $pdo->lastInsertId();
            setTourCategories($tourId, $_POST['categories'] ?? []);
            ob_end_clean();
            header('Location: /admin/tours.php');
            exit;
        } catch (PDOException $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
            $formFeatures = $submittedFeatures ?: $defaultFeatures;
        }
    } else {
        $formFeatures = $submittedFeatures ?: $defaultFeatures;
    }
}

$imgList = $tour ? implode("\n", json_decode($tour['images'] ?? '[]', true)) : '';

$iconOptions = [
    'fa-star'           => '⭐ Star',
    'fa-shield-alt'     => '🛡 Shield',
    'fa-undo'           => '↩ Cancellation',
    'fa-check'          => '✔ Check',
    'fa-clock'          => '🕐 Clock',
    'fa-users'          => '👥 Users',
    'fa-map-marker-alt' => '📍 Location',
    'fa-heart'          => '❤ Heart',
    'fa-globe'          => '🌍 Globe',
    'fa-wifi'           => '📶 Wi-Fi',
    'fa-utensils'       => '🍴 Meals',
    'fa-car'            => '🚗 Car',
    'fa-plane'          => '✈ Plane',
    'fa-hotel'          => '🏨 Hotel',
    'fa-camera'         => '📷 Camera',
    'fa-leaf'           => '🌿 Eco',
    'fa-award'          => '🏆 Award',
    'fa-binoculars'     => '🔭 Safari',
    'fa-sun'            => '☀ Outdoor',
    'fa-fire'           => '🔥 Hot Deal',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $tour ? 'Edit' : 'Add'; ?> Tour | Bamba Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
    <link href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css" rel="stylesheet">
    <style>
        .feature-row { display: flex; align-items: center; gap: 0.75rem; padding: 0.65rem 0.75rem; background: #f9f9f9; border: 1px solid #eee; border-radius: 10px; margin-bottom: 0.6rem; }
        .feature-row input[type="checkbox"] { width: 17px; height: 17px; accent-color: var(--primary); flex-shrink: 0; }
        .feature-row select { padding: 0.4rem 0.5rem; border: 1px solid #ddd; border-radius: 6px; font-size: 0.85rem; background: #fff; }
        .feature-row input[type="text"] { flex: 1; padding: 0.4rem 0.7rem; border: 1px solid #ddd; border-radius: 6px; font-size: 0.9rem; font-family: inherit; }
        .feature-row .remove-feature { background: none; border: none; color: #ccc; cursor: pointer; font-size: 1rem; padding: 0 0.25rem; transition: color 0.2s; flex-shrink: 0; }
        .feature-row .remove-feature:hover { color: #e53e3e; }
        .add-feature-btn { background: none; border: 2px dashed #ddd; border-radius: 10px; padding: 0.6rem 1.2rem; font-size: 0.88rem; color: var(--text-light); cursor: pointer; width: 100%; margin-top: 0.4rem; transition: all 0.2s; font-family: inherit; }
        .add-feature-btn:hover { border-color: var(--primary); color: var(--primary); }
        .feature-icon-preview { width: 28px; text-align: center; color: var(--primary); font-size: 0.95rem; flex-shrink: 0; }
        .cat-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 0.6rem; }
        .cat-item { display: flex; align-items: center; gap: 0.5rem; padding: 0.55rem 0.75rem; background: #f9f9f9; border: 1.5px solid #eee; border-radius: 8px; cursor: pointer; transition: all 0.2s; }
        .cat-item:hover { border-color: var(--primary); background: #fff7f0; }
        .cat-item input[type="checkbox"] { width: 16px; height: 16px; accent-color: var(--primary); }
        .cat-item.checked { border-color: var(--primary); background: #fff3ea; }
        .slug-wrap { position: relative; }
        .slug-wrap input { padding-right: 2.2rem; }
        .slug-sync-icon { position: absolute; right: 0.65rem; top: 50%; transform: translateY(-50%); color: #bbb; font-size: 0.85rem; pointer-events: none; transition: color 0.2s; }
        .slug-sync-icon.syncing { color: var(--primary); }
        .quill-field { border: 1.5px solid var(--border); border-radius: 8px; overflow: hidden; margin-top: 0.25rem; }
        .quill-field:focus-within { border-color: var(--primary); }
        .quill-field .ql-toolbar { background: #fafafa; }
        .quill-field .ql-editor { min-height: 220px; font-family: 'Inter', sans-serif; font-size: 0.92rem; line-height: 1.7; color: #333; }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main">
        <div class="topbar">
            <h1><?php echo $tour ? 'Edit' : 'Add'; ?> Tour</h1>
            <a href="tours.php" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Back</a>
        </div>
        <div class="content">
            <?php if ($errors): ?>
            <div class="alert alert-error"><?php echo implode('<br>', array_map('htmlspecialchars', $errors)); ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" id="tourForm">
            <?php echo csrf_field(); ?>

                <!-- ── Tour Details ─────────────────────────────────── -->
                <div class="card">
                    <div class="card-header"><h2>Tour Details</h2></div>
                    <div class="card-body">
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Title *</label>
                                <input type="text" id="tourTitle" name="title" value="<?php echo esc($tour['title'] ?? ''); ?>" required autocomplete="off">
                            </div>
                            <div class="form-group">
                                <label>Slug <small style="color:var(--text-light);font-weight:400;">(auto-filled from title)</small></label>
                                <div class="slug-wrap">
                                    <input type="text" id="tourSlug" name="slug" value="<?php echo esc($tour['slug'] ?? ''); ?>" placeholder="auto-generated from title" autocomplete="off">
                                    <i class="fas fa-link slug-sync-icon" id="slugIcon"></i>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Destination</label>
                                <select name="destination_id">
                                    <option value="">-- Select --</option>
                                    <?php foreach ($destinations as $d): ?>
                                    <option value="<?php echo $d['id']; ?>" <?php echo ($tour['destination_id'] ?? '') == $d['id'] ? 'selected' : ''; ?>><?php echo esc($d['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Country</label>
                                <select name="country_id">
                                    <option value="">-- Select --</option>
                                    <?php foreach ($countries as $c): ?>
                                    <option value="<?php echo $c['id']; ?>" <?php echo ($tour['country_id'] ?? '') == $c['id'] ? 'selected' : ''; ?>><?php echo esc($c['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Duration</label>
                                <input type="text" name="duration" value="<?php echo esc($tour['duration'] ?? ''); ?>" placeholder="e.g. 7 Days / 6 Nights">
                            </div>
                            <div class="form-group">
                                <label>Price (USD)</label>
                                <input type="number" name="price" step="0.01" value="<?php echo esc($tour['price'] ?? ''); ?>" placeholder="e.g. 2500">
                            </div>
                            <div class="form-group">
                                <label>Price Note</label>
                                <input type="text" name="price_note" value="<?php echo esc($tour['price_note'] ?? ''); ?>" placeholder="e.g. per person sharing">
                            </div>
                            <div class="form-group">
                                <label>Max Group Size</label>
                                <input type="number" name="max_group_size" value="<?php echo esc($tour['max_group_size'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label>Status</label>
                                <select name="status">
                                    <option value="active" <?php echo ($tour['status'] ?? 'draft') === 'active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="draft"  <?php echo ($tour['status'] ?? 'draft') === 'draft'  ? 'selected' : ''; ?>>Draft</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Featured</label>
                                <select name="featured">
                                    <option value="no"  <?php echo ($tour['featured'] ?? 'no') === 'no'  ? 'selected' : ''; ?>>No</option>
                                    <option value="yes" <?php echo ($tour['featured'] ?? 'no') === 'yes' ? 'selected' : ''; ?>>Yes</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ── Categories ──────────────────────────────────── -->
                <div class="card">
                    <div class="card-header"><h2>Categories</h2></div>
                    <div class="card-body">
                        <p style="color:var(--text-light);font-size:0.88rem;margin-bottom:1.25rem;">Select all categories this tour belongs to.</p>
                        <?php if (empty($allCategories)): ?>
                        <p style="color:var(--text-light);font-style:italic;">No categories found. <a href="categories.php" style="color:var(--primary);">Create categories</a> first.</p>
                        <?php else: ?>
                        <div class="cat-grid" id="catGrid">
                            <?php foreach ($allCategories as $cat):
                                $checked = in_array($cat['id'], $tourCategoryIds);
                            ?>
                            <label class="cat-item <?php echo $checked ? 'checked' : ''; ?>">
                                <input type="checkbox" name="categories[]" value="<?php echo $cat['id']; ?>" <?php echo $checked ? 'checked' : ''; ?>>
                                <?php echo esc($cat['name']); ?>
                            </label>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- ── Tour Features ────────────────────────────────── -->
                <div class="card">
                    <div class="card-header"><h2>Tour Features</h2></div>
                    <div class="card-body">
                        <p style="color:var(--text-light);font-size:0.88rem;margin-bottom:1.25rem;">Trust badges shown on the tour sidebar. Tick to show, untick to hide.</p>
                        <div id="featuresContainer">
                            <?php foreach ($formFeatures as $i => $feat): ?>
                            <div class="feature-row">
                                <input type="checkbox" name="feature_enabled[<?php echo $i; ?>]" value="1" <?php echo $feat['enabled'] ? 'checked' : ''; ?>>
                                <span class="feature-icon-preview"><i class="fas <?php echo esc($feat['icon']); ?>"></i></span>
                                <select name="feature_icon[<?php echo $i; ?>]" onchange="updateIcon(this)">
                                    <?php foreach ($iconOptions as $cls => $label): ?>
                                    <option value="<?php echo $cls; ?>" <?php echo $feat['icon'] === $cls ? 'selected' : ''; ?>><?php echo $label; ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="text" name="feature_label[<?php echo $i; ?>]" value="<?php echo esc($feat['label']); ?>" placeholder="Feature label">
                                <button type="button" class="remove-feature" onclick="removeFeatureRow(this)" title="Remove"><i class="fas fa-times"></i></button>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" class="add-feature-btn" onclick="addFeatureRow()">
                            <i class="fas fa-plus"></i> Add Feature
                        </button>
                    </div>
                </div>

                <!-- ── Content ──────────────────────────────────────── -->
                <div class="card">
                    <div class="card-header"><h2>Content</h2></div>
                    <div class="card-body">
                        <div class="form-group full">
                            <label>Description</label>
                            <textarea name="description" id="ed_description" class="rich-editor" rows="6"><?php echo esc($tour['description'] ?? ''); ?></textarea>
                        </div>
                        <div class="form-group full">
                            <label>Highlights</label>
                            <textarea name="highlights" id="ed_highlights" class="rich-editor" rows="5"><?php echo esc($tour['highlights'] ?? ''); ?></textarea>
                        </div>
                        <div class="form-group full">
                            <label>Day-by-Day Itinerary</label>
                            <textarea name="itinerary" id="ed_itinerary" class="rich-editor" rows="8"><?php echo esc($tour['itinerary'] ?? ''); ?></textarea>
                        </div>
                        <div class="form-group full">
                            <label>Inclusions</label>
                            <textarea name="inclusions" id="ed_inclusions" class="rich-editor" rows="5"><?php echo esc($tour['inclusions'] ?? ''); ?></textarea>
                        </div>
                        <div class="form-group full">
                            <label>Exclusions</label>
                            <textarea name="exclusions" id="ed_exclusions" class="rich-editor" rows="5"><?php echo esc($tour['exclusions'] ?? ''); ?></textarea>
                        </div>
                        <div class="form-group full">
                            <label>Seasonal Notes <small style="color:var(--text-light);font-weight:400;">— seasonal tips, conditions, best time to visit</small></label>
                            <textarea name="service_notes" id="ed_service_notes" class="rich-editor" rows="4"><?php echo esc($tour['service_notes'] ?? ''); ?></textarea>
                        </div>
                        <div class="form-group full">
                            <label>Optional Add-ons <small style="color:var(--text-light);font-weight:400;">— extra activities or upgrades available at extra cost</small></label>
                            <textarea name="optional_addons" id="ed_optional_addons" class="rich-editor" rows="4"><?php echo esc($tour['optional_addons'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- ── Images ───────────────────────────────────────── -->
                <div class="card">
                    <div class="card-header"><h2>Images</h2></div>
                    <div class="card-body">
                        <div class="form-group full">
                            <label>Upload Images</label>
                            <input type="file" name="tour_images[]" multiple accept="image/*">
                            <small>Select multiple images. First image is used as the cover / thumbnail.</small>
                        </div>
                        <div class="form-group full">
                            <label>Or paste Image URLs (one per line)</label>
                            <textarea name="existing_images" rows="4" placeholder="https://example.com/image1.jpg&#10;https://example.com/image2.jpg"><?php echo esc($imgList); ?></textarea>
                            <small>Existing images shown here. Remove a line to delete. Uploads above will be appended.</small>
                        </div>
                    </div>
                </div>

                <!-- ── Advanced SEO (collapsible) ──────────────────── -->
                <details class="seo-details" <?php echo (!empty($tour['meta_title']) || !empty($tour['meta_description'])) ? 'open' : ''; ?>>
                    <summary class="seo-summary">
                        <i class="fas fa-search"></i> Advanced SEO
                        <span class="seo-note">— optional, auto-generated from tour title &amp; description if left empty</span>
                        <i class="fas fa-chevron-down seo-chevron"></i>
                    </summary>
                    <div class="seo-body">
                        <div class="form-group">
                            <label>Meta Title <small style="color:var(--text-light);font-weight:400;">— overrides auto-generated title in search results</small></label>
                            <input type="text" name="meta_title" value="<?php echo esc($tour['meta_title'] ?? ''); ?>" placeholder="Leave empty to auto-generate: <?php echo esc($tour['title'] ?? 'Tour Title'); ?> | Bamba Adventures">
                        </div>
                        <div class="form-group">
                            <label>Meta Description <small style="color:var(--text-light);font-weight:400;">— overrides auto-generated snippet in search results</small></label>
                            <textarea name="meta_description" rows="2" placeholder="Leave empty to auto-generate from description (first 155 characters)"><?php echo esc($tour['meta_description'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </details>

                <div style="display:flex;gap:1rem;margin-top:1rem;">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Tour</button>
                    <a href="tours.php" class="btn btn-outline">Cancel</a>
                </div>

            </form>
        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>
<script>
/* ── Quill rich-text editors ────────────────────────────────── */
var quillToolbar = [
    [{ 'header': [2, 3, 4, false] }],
    ['bold', 'italic', 'underline', 'strike'],
    [{ 'list': 'ordered' }, { 'list': 'bullet' }],
    [{ 'indent': '-1' }, { 'indent': '+1' }],
    ['link', 'clean']
];

var quillMap = {};   // id → { quill, textarea }

document.querySelectorAll('textarea.rich-editor').forEach(function(textarea) {
    // Wrap: insert a styled div then hide the textarea
    var wrapper = document.createElement('div');
    wrapper.className = 'quill-field';
    textarea.parentNode.insertBefore(wrapper, textarea);
    textarea.style.display = 'none';

    var quill = new Quill(wrapper, {
        theme: 'snow',
        modules: { toolbar: quillToolbar }
    });

    // Load existing content
    if (textarea.value.trim()) {
        quill.root.innerHTML = textarea.value;
    }

    quillMap[textarea.id] = { quill: quill, textarea: textarea };
});

/* Copy Quill content back to hidden textareas on submit */
document.getElementById('tourForm').addEventListener('submit', function() {
    Object.values(quillMap).forEach(function(item) {
        var html = item.quill.root.innerHTML;
        item.textarea.value = (html === '<p><br></p>') ? '' : html;
    });
});

/* ── Slug auto-fill from title ──────────────────── */
const titleInput = document.getElementById('tourTitle');
const slugInput  = document.getElementById('tourSlug');
const slugIcon   = document.getElementById('slugIcon');

function toSlug(str) {
    return str.toLowerCase()
        .replace(/[^a-z0-9\s-]/g, '')
        .trim()
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-');
}

let slugManual = false;
<?php if ($tour && !empty($tour['slug'])): ?>
slugInput.addEventListener('input', () => {
    slugManual = slugInput.value !== '' && slugInput.value !== toSlug(titleInput.value);
    slugIcon.classList.toggle('syncing', !slugManual);
});
<?php endif; ?>

titleInput.addEventListener('input', function () {
    if (!slugManual) {
        slugInput.value = toSlug(this.value);
        slugIcon.classList.add('syncing');
    }
});

titleInput.addEventListener('blur', function () {
    if (!slugManual && this.value) slugInput.value = toSlug(this.value);
});

slugInput.addEventListener('input', function () {
    if (this.value === '') {
        slugManual = false;
        slugIcon.classList.add('syncing');
    } else if (this.value !== toSlug(titleInput.value)) {
        slugManual = true;
        slugIcon.classList.remove('syncing');
    }
});

<?php if (!$tour): ?>
slugIcon.classList.add('syncing');
<?php endif; ?>

/* ── Category item highlight on check ──────────── */
document.querySelectorAll('.cat-item input[type="checkbox"]').forEach(cb => {
    cb.addEventListener('change', function () {
        this.closest('.cat-item').classList.toggle('checked', this.checked);
    });
});

/* ── Features: icon preview update ─────────────── */
function updateIcon(select) {
    const preview = select.closest('.feature-row').querySelector('.feature-icon-preview i');
    preview.className = 'fas ' + select.value;
}

/* ── Features: remove row ───────────────────────── */
function removeFeatureRow(btn) {
    btn.closest('.feature-row').remove();
    reindexFeatures();
}

/* ── Features: add new row ──────────────────────── */
function addFeatureRow() {
    const container = document.getElementById('featuresContainer');
    const idx = container.querySelectorAll('.feature-row').length;
    const iconOpts = <?php echo json_encode(array_map(fn($k,$v) => ['value'=>$k,'label'=>$v], array_keys($iconOptions), array_values($iconOptions))); ?>;
    const optHtml = iconOpts.map(o => `<option value="${o.value}">${o.label}</option>`).join('');

    const row = document.createElement('div');
    row.className = 'feature-row';
    row.innerHTML = `
        <input type="checkbox" name="feature_enabled[${idx}]" value="1" checked>
        <span class="feature-icon-preview"><i class="fas fa-check"></i></span>
        <select name="feature_icon[${idx}]" onchange="updateIcon(this)">${optHtml}</select>
        <input type="text" name="feature_label[${idx}]" placeholder="Feature label e.g. Free Airport Transfer">
        <button type="button" class="remove-feature" onclick="removeFeatureRow(this)" title="Remove"><i class="fas fa-times"></i></button>
    `;
    container.appendChild(row);
}

/* ── Re-index feature names after removal ───────── */
function reindexFeatures() {
    document.querySelectorAll('#featuresContainer .feature-row').forEach((row, i) => {
        const cb  = row.querySelector('input[type="checkbox"]');
        const sel = row.querySelector('select');
        const inp = row.querySelector('input[type="text"]');
        if (cb)  cb.name  = `feature_enabled[${i}]`;
        if (sel) sel.name = `feature_icon[${i}]`;
        if (inp) inp.name = `feature_label[${i}]`;
    });
}
</script>

</body>
</html>
