<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$service = null;
$errors = [];

if (!empty($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM services WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $service = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $data = [
        'name' => trim($_POST['name'] ?? ''),
        'slug' => slugify(trim($_POST['slug'] ?? $_POST['name'] ?? '')),
        'icon' => trim($_POST['icon'] ?? 'fas fa-star'),
        'description' => trim($_POST['description'] ?? ''),
        'long_content' => trim($_POST['long_content'] ?? ''),
        'image' => trim($_POST['image'] ?? ''),
        'meta_title' => trim($_POST['meta_title'] ?? ''),
        'meta_description' => trim($_POST['meta_description'] ?? ''),
        'status' => $_POST['status'] ?? 'active',
        'featured' => $_POST['featured'] ?? 'no',
        'sort_order' => intval($_POST['sort_order'] ?? 0),
    ];
    
    if (empty($data['name'])) $errors[] = 'Name is required';
    
    // Handle hero image upload
    if (!empty($_FILES['hero_image']) && $_FILES['hero_image']['tmp_name'] && $_FILES['hero_image']['error'] === UPLOAD_ERR_OK) {
        $result = uploadImage($_FILES['hero_image'], 'services');
        if (!empty($result['success'])) {
            $data['image'] = '/' . $result['path'];
        }
    }
    
    if (empty($errors)) {
        if ($service) {
            $sql = "UPDATE services SET name=:name, slug=:slug, icon=:icon, description=:description, long_content=:long_content, image=:image, meta_title=:meta_title, meta_description=:meta_description, status=:status, featured=:featured, sort_order=:sort_order WHERE id=:id";
            $data['id'] = $service['id'];
            $stmt = $pdo->prepare($sql);
            $stmt->execute($data);
        } else {
            $sql = "INSERT INTO services (name, slug, icon, description, long_content, image, meta_title, meta_description, status, featured, sort_order) VALUES (:name, :slug, :icon, :description, :long_content, :image, :meta_title, :meta_description, :status, :featured, :sort_order)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($data);
        }
        header('Location: /admin/services.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $service ? 'Edit' : 'Add'; ?> Service | Bamba Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main">
        <div class="topbar">
            <h1><?php echo $service ? 'Edit' : 'Add'; ?> Service</h1>
            <a href="services.php" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Back</a>
        </div>
        <div class="content">
            <?php if ($errors): ?>
            <div class="alert alert-error"><?php echo implode('<br>', $errors); ?></div>
            <?php endif; ?>
            <form method="POST" enctype="multipart/form-data">
            <?php echo csrf_field(); ?>
                <div class="card">
                    <div class="card-header"><h2>Service Details</h2></div>
                    <div class="card-body">
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Name *</label>
                                <input type="text" name="name" value="<?php echo esc($service['name'] ?? ''); ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Slug</label>
                                <input type="text" name="slug" value="<?php echo esc($service['slug'] ?? ''); ?>" placeholder="auto-generated if empty">
                            </div>
                            <div class="form-group">
                                <label>Icon Class</label>
                                <input type="text" name="icon" value="<?php echo esc($service['icon'] ?? 'fas fa-star'); ?>">
                            </div>
                            <div class="form-group">
                                <label>Status</label>
                                <select name="status">
                                    <option value="active" <?php echo ($service['status'] ?? '') === 'active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="draft" <?php echo ($service['status'] ?? '') === 'draft' ? 'selected' : ''; ?>>Draft</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Featured on Homepage</label>
                                <select name="featured">
                                    <option value="no" <?php echo ($service['featured'] ?? '') === 'no' ? 'selected' : ''; ?>>No</option>
                                    <option value="yes" <?php echo ($service['featured'] ?? '') === 'yes' ? 'selected' : ''; ?>>Yes</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Sort Order</label>
                                <input type="number" name="sort_order" value="<?php echo esc($service['sort_order'] ?? 0); ?>">
                            </div>
                            <div class="form-group">
                                <label>Hero Image</label>
                                <input type="file" name="hero_image" accept="image/*">
                                <?php if (!empty($service['image'])): ?>
                                <small>Current: <a href="<?php echo esc($service['image']); ?>" target="_blank"><?php echo esc($service['image']); ?></a></small>
                                <?php endif; ?>
                            </div>
                            <div class="form-group">
                                <label>Or Image URL</label>
                                <input type="text" name="image" value="<?php echo esc($service['image'] ?? ''); ?>">
                            </div>
                            <div class="form-group full">
                                <label>Short Description</label>
                                <textarea name="description" rows="3"><?php echo esc($service['description'] ?? ''); ?></textarea>
                            </div>
                            <div class="form-group full">
                                <label>Long Content</label>
                                <textarea name="long_content" rows="6"><?php echo esc($service['long_content'] ?? ''); ?></textarea>
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
                                <input type="text" name="meta_title" value="<?php echo esc($service['meta_title'] ?? ''); ?>">
                            </div>
                            <div class="form-group full">
                                <label>Meta Description</label>
                                <textarea name="meta_description" rows="2"><?php echo esc($service['meta_description'] ?? ''); ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div style="display:flex;gap:1rem;margin-top:1rem;">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Service</button>
                    <a href="services.php" class="btn btn-outline">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
