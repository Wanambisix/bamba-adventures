<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$id = $_GET['id'] ?? '';
$page = null;
if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM pages WHERE id = ?");
    $stmt->execute([$id]);
    $page = $stmt->fetch();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $data = [
        'title' => trim($_POST['title'] ?? ''),
        'slug' => slugify(trim($_POST['slug'] ?? '')),
        'content' => $_POST['content'] ?? '',
        'icon' => trim($_POST['icon'] ?? 'fas fa-file'),
        'show_in_nav' => $_POST['show_in_nav'] ?? 'yes',
        'meta_title' => trim($_POST['meta_title'] ?? ''),
        'meta_description' => trim($_POST['meta_description'] ?? ''),
        'status' => $_POST['status'] ?? 'active',
        'sort_order' => (int)($_POST['sort_order'] ?? 0),
    ];
    
    if (empty($data['title']) || empty($data['slug'])) {
        $error = 'Title and slug are required.';
    } else {
        try {
            if ($id) {
                $pdo->prepare("UPDATE pages SET title=?, slug=?, content=?, icon=?, show_in_nav=?, meta_title=?, meta_description=?, status=?, sort_order=? WHERE id=?")
                    ->execute([$data['title'], $data['slug'], $data['content'], $data['icon'], $data['show_in_nav'], $data['meta_title'], $data['meta_description'], $data['status'], $data['sort_order'], $id]);
                $_SESSION['flash_message'] = 'Page updated successfully.';
            } else {
                $pdo->prepare("INSERT INTO pages (title, slug, content, icon, show_in_nav, meta_title, meta_description, status, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)")
                    ->execute([$data['title'], $data['slug'], $data['content'], $data['icon'], $data['show_in_nav'], $data['meta_title'], $data['meta_description'], $data['status'], $data['sort_order']]);
                $_SESSION['flash_message'] = 'Page created successfully.';
            }
            header('Location: /admin/pages.php');
            exit;
        } catch (PDOException $e) {
            $error = 'Error saving page: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $id ? 'Edit' : 'New'; ?> Page | Bamba Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
    <link href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css" rel="stylesheet">
    <style>
        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; font-weight: 600; margin-bottom: 0.5rem; font-size: 0.9rem; }
        .form-group input[type="text"], .form-group select, .form-group textarea { width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 8px; font-family: inherit; font-size: 0.95rem; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
        .hint { color: #888; font-size: 0.85rem; margin-top: 0.25rem; }
        .quill-field { border: 1.5px solid var(--border); border-radius: 8px; overflow: hidden; }
        .quill-field .ql-toolbar { background: #fafafa; }
        .quill-field .ql-editor { min-height: 400px; font-family: 'Inter', sans-serif; font-size: 0.92rem; line-height: 1.7; color: #333; }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main">
        <div class="topbar">
            <h1><?php echo $id ? 'Edit Page' : 'New Page'; ?></h1>
            <a href="/admin/pages.php" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Back to Pages</a>
        </div>
        <div class="content">
            <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo esc($error); ?></div>
            <?php endif; ?>

            <form method="POST">
            <?php echo csrf_field(); ?>
                <div class="form-row">
                    <div class="form-group">
                        <label>Page Title</label>
                        <input type="text" name="title" value="<?php echo esc($page['title'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>URL Slug</label>
                        <input type="text" name="slug" value="<?php echo esc($page['slug'] ?? ''); ?>" required>
                        <div class="hint">Will be accessible at /page/your-slug</div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Content</label>
                    <div id="pageEditor" class="quill-field"></div>
                    <textarea name="content" id="pageContent" style="display:none;"><?php echo esc($page['content'] ?? ''); ?></textarea>
                    <div class="hint" style="margin-top:0.4rem;">Use the editor toolbar for formatting. Content is saved as HTML and rendered on the public page.</div>
                </div>

                <details class="seo-details">
                    <summary class="seo-summary">
                        <i class="fas fa-search"></i> Advanced SEO
                        <span class="seo-note">— optional, auto-generated from page title if left empty</span>
                        <i class="fas fa-chevron-down seo-chevron"></i>
                    </summary>
                    <div class="seo-body">
                        <div class="form-group">
                            <label>Meta Title <small style="color:var(--text-light);font-weight:400;">— overrides auto-generated title in search results</small></label>
                            <input type="text" name="meta_title" value="<?php echo esc($page['meta_title'] ?? ''); ?>" placeholder="Leave empty to auto-generate: <?php echo esc($page['title'] ?? 'Page Title'); ?> | Bamba Adventures">
                        </div>
                        <div class="form-group">
                            <label>Meta Description <small style="color:var(--text-light);font-weight:400;">— overrides auto-generated snippet in search results</small></label>
                            <textarea name="meta_description" rows="2" placeholder="Leave empty to auto-generate from page content (first 155 characters)"><?php echo esc($page['meta_description'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </details>

                <div class="form-row">
                    <div class="form-group">
                        <label>Icon Class</label>
                        <input type="text" name="icon" value="<?php echo esc($page['icon'] ?? 'fas fa-file'); ?>">
                        <div class="hint">Font Awesome icon, e.g. fas fa-info-circle</div>
                    </div>
                    <div class="form-group">
                        <label>Sort Order</label>
                        <input type="number" name="sort_order" value="<?php echo (int)($page['sort_order'] ?? 0); ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Show in Navigation</label>
                        <select name="show_in_nav">
                            <option value="yes" <?php echo ($page['show_in_nav'] ?? 'yes') === 'yes' ? 'selected' : ''; ?>>Yes</option>
                            <option value="no" <?php echo ($page['show_in_nav'] ?? '') === 'no' ? 'selected' : ''; ?>>No</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status">
                            <option value="active" <?php echo ($page['status'] ?? 'active') === 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="draft" <?php echo ($page['status'] ?? '') === 'draft' ? 'selected' : ''; ?>>Draft</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary"><?php echo $id ? 'Update Page' : 'Create Page'; ?></button>
                <a href="/admin/pages.php" class="btn btn-outline">Cancel</a>
            </form>
        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>
<script>
var ta = document.getElementById('pageContent');
var quill = new Quill('#pageEditor', {
    theme: 'snow',
    modules: {
        toolbar: [
            [{ 'header': [2, 3, 4, false] }],
            ['bold', 'italic', 'underline', 'strike'],
            [{ 'list': 'ordered' }, { 'list': 'bullet' }],
            [{ 'indent': '-1' }, { 'indent': '+1' }],
            ['link', 'blockquote', 'clean']
        ]
    }
});

if (ta.value.trim()) {
    quill.root.innerHTML = ta.value;
}

document.querySelector('form').addEventListener('submit', function() {
    var html = quill.root.innerHTML;
    ta.value = (html === '<p><br></p>') ? '' : html;
});

</script>

</body>
</html>
