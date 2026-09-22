<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$destination = null;
$errors = [];

if (!empty($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM destinations WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $destination = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $data = [
        'name' => trim($_POST['name'] ?? ''),
        'slug' => slugify(trim($_POST['slug'] ?? $_POST['name'] ?? '')),
        'description' => trim($_POST['description'] ?? ''),
        'long_content' => trim($_POST['long_content'] ?? ''),
        'image' => trim($_POST['image'] ?? ''),
        'hero_image' => trim($_POST['hero_image'] ?? ''),
        'meta_title' => trim($_POST['meta_title'] ?? ''),
        'meta_description' => trim($_POST['meta_description'] ?? ''),
        'status' => $_POST['status'] ?? 'active',
        'featured' => $_POST['featured'] ?? 'no',
        'sort_order' => intval($_POST['sort_order'] ?? 0),
    ];
    
    // Handle image uploads
    if (!empty($_FILES['image_file']['tmp_name']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
        $result = uploadImage($_FILES['image_file'], 'destinations');
        if (!empty($result['success'])) $data['image'] = '/' . $result['path'];
    } elseif (empty($data['image'])) {
        $data['image'] = $destination['image'] ?? '';
    }
    if (!empty($_FILES['hero_image_file']['tmp_name']) && $_FILES['hero_image_file']['error'] === UPLOAD_ERR_OK) {
        $result = uploadImage($_FILES['hero_image_file'], 'destinations');
        if (!empty($result['success'])) $data['hero_image'] = '/' . $result['path'];
    } elseif (empty($data['hero_image'])) {
        $data['hero_image'] = $destination['hero_image'] ?? '';
    }

    if (empty($data['name'])) $errors[] = 'Name is required';

    if (empty($errors)) {
        if ($destination) {
            $sql = "UPDATE destinations SET name=:name, slug=:slug, description=:description, long_content=:long_content, image=:image, hero_image=:hero_image, meta_title=:meta_title, meta_description=:meta_description, status=:status, featured=:featured, sort_order=:sort_order WHERE id=:id";
            $data['id'] = $destination['id'];
            $stmt = $pdo->prepare($sql);
            $stmt->execute($data);
        } else {
            $sql = "INSERT INTO destinations (name, slug, description, long_content, image, hero_image, meta_title, meta_description, status, featured, sort_order) VALUES (:name, :slug, :description, :long_content, :image, :hero_image, :meta_title, :meta_description, :status, :featured, :sort_order)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($data);
        }
        header('Location: /admin/destinations.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $destination ? 'Edit' : 'Add'; ?> Destination | Bamba Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main">
        <div class="topbar">
            <h1><?php echo $destination ? 'Edit' : 'Add'; ?> Destination</h1>
            <a href="destinations.php" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Back</a>
        </div>
        <div class="content">
            <?php if ($errors): ?>
            <div class="alert alert-error"><?php echo implode('<br>', $errors); ?></div>
            <?php endif; ?>
            <form method="POST" enctype="multipart/form-data">
            <?php echo csrf_field(); ?>
                <div class="card">
                    <div class="card-header"><h2>Destination Details</h2></div>
                    <div class="card-body">
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Name *</label>
                                <input type="text" name="name" value="<?php echo esc($destination['name'] ?? ''); ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Slug</label>
                                <input type="text" name="slug" value="<?php echo esc($destination['slug'] ?? ''); ?>" placeholder="auto-generated if empty">
                            </div>
                            <div class="form-group">
                                <label>Status</label>
                                <select name="status">
                                    <option value="active" <?php echo ($destination['status'] ?? '') === 'active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="draft" <?php echo ($destination['status'] ?? '') === 'draft' ? 'selected' : ''; ?>>Draft</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Featured on Homepage</label>
                                <select name="featured">
                                    <option value="no" <?php echo ($destination['featured'] ?? '') === 'no' ? 'selected' : ''; ?>>No</option>
                                    <option value="yes" <?php echo ($destination['featured'] ?? '') === 'yes' ? 'selected' : ''; ?>>Yes</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Sort Order</label>
                                <input type="number" name="sort_order" value="<?php echo esc($destination['sort_order'] ?? 0); ?>">
                            </div>
                            <div class="form-group full">
                                <label>Short Description</label>
                                <textarea name="description" rows="3"><?php echo esc($destination['description'] ?? ''); ?></textarea>
                            </div>
                            <div class="form-group full">
                                <label>Long Content</label>
                                <textarea name="long_content" rows="6"><?php echo esc($destination['long_content'] ?? ''); ?></textarea>
                            </div>
                            <div class="form-group">
                                <label>Card Image</label>
                                <input type="file" name="image_file" accept="image/*">
                                <?php if (!empty($destination['image'])): ?>
                                <div style="margin-top:0.5rem;"><img src="<?php echo esc($destination['image']); ?>" alt="Current" style="max-height:80px;border-radius:6px;"></div>
                                <?php endif; ?>
                                <label style="margin-top:0.75rem;font-size:0.85rem;color:#888;">Or paste URL</label>
                                <input type="text" name="image" value="<?php echo esc($destination['image'] ?? ''); ?>" placeholder="https://...">
                            </div>
                            <div class="form-group">
                                <label>Hero Image</label>
                                <input type="file" name="hero_image_file" accept="image/*">
                                <?php if (!empty($destination['hero_image'])): ?>
                                <div style="margin-top:0.5rem;"><img src="<?php echo esc($destination['hero_image']); ?>" alt="Current" style="max-height:80px;border-radius:6px;"></div>
                                <?php endif; ?>
                                <label style="margin-top:0.75rem;font-size:0.85rem;color:#888;">Or paste URL</label>
                                <input type="text" name="hero_image" value="<?php echo esc($destination['hero_image'] ?? ''); ?>" placeholder="https://...">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header"><h2>SEO</h2></div>
                    <div class="card-body">
                        <div class="form-grid">
                            <div class="form-group full">
                                <label>Meta Title</label>
                                <input type="text" name="meta_title" value="<?php echo esc($destination['meta_title'] ?? ''); ?>">
                            </div>
                            <div class="form-group full">
                                <label>Meta Description</label>
                                <textarea name="meta_description" rows="2"><?php echo esc($destination['meta_description'] ?? ''); ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div style="display:flex;gap:1rem;margin-top:1rem;">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Destination</button>
                    <a href="destinations.php" class="btn btn-outline">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
