<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$destinations = getDestinations();
$country = null;
$errors = [];

if (!empty($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM countries WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $country = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $data = [
        'destination_id' => $_POST['destination_id'] ?: null,
        'name' => trim($_POST['name'] ?? ''),
        'slug' => slugify(trim($_POST['slug'] ?? $_POST['name'] ?? '')),
        'description' => trim($_POST['description'] ?? ''),
        'status' => $_POST['status'] ?? 'active',
        'sort_order' => intval($_POST['sort_order'] ?? 0),
    ];
    
    // Handle image upload
    if (!empty($_FILES['hero_image']) && $_FILES['hero_image']['tmp_name'] && $_FILES['hero_image']['error'] === UPLOAD_ERR_OK) {
        $result = uploadImage($_FILES['hero_image'], 'countries');
        if (!empty($result['success'])) {
            $data['image'] = '/' . $result['path'];
        }
    } elseif (!empty($_POST['image'])) {
        $data['image'] = trim($_POST['image']);
    } else {
        $data['image'] = $country['image'] ?? '';
    }
    
    if (empty($data['name'])) $errors[] = 'Name is required';
    if (empty($data['destination_id'])) $errors[] = 'Destination is required';
    
    if (empty($errors)) {
        if ($country) {
            $sql = "UPDATE countries SET destination_id=:destination_id, name=:name, slug=:slug, description=:description, image=:image, status=:status, sort_order=:sort_order WHERE id=:id";
            $data['id'] = $country['id'];
            $stmt = $pdo->prepare($sql);
            $stmt->execute($data);
        } else {
            $sql = "INSERT INTO countries (destination_id, name, slug, description, image, status, sort_order) VALUES (:destination_id, :name, :slug, :description, :image, :status, :sort_order)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($data);
        }
        header('Location: /admin/countries.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $country ? 'Edit' : 'Add'; ?> Country | Bamba Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main">
        <div class="topbar">
            <h1><?php echo $country ? 'Edit' : 'Add'; ?> Country</h1>
            <a href="countries.php" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Back</a>
        </div>
        <div class="content">
            <?php if ($errors): ?>
            <div class="alert alert-error"><?php echo implode('<br>', $errors); ?></div>
            <?php endif; ?>
            <form method="POST" enctype="multipart/form-data">
            <?php echo csrf_field(); ?>
                <div class="card">
                    <div class="card-header"><h2>Country Details</h2></div>
                    <div class="card-body">
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Destination *</label>
                                <select name="destination_id" required>
                                    <option value="">-- Select --</option>
                                    <?php foreach ($destinations as $d): ?>
                                    <option value="<?php echo $d['id']; ?>" <?php echo ($country['destination_id'] ?? '') == $d['id'] ? 'selected' : ''; ?>><?php echo esc($d['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Name *</label>
                                <input type="text" name="name" value="<?php echo esc($country['name'] ?? ''); ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Slug</label>
                                <input type="text" name="slug" value="<?php echo esc($country['slug'] ?? ''); ?>" placeholder="auto-generated if empty">
                            </div>
                            <div class="form-group">
                                <label>Status</label>
                                <select name="status">
                                    <option value="active" <?php echo ($country['status'] ?? '') === 'active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="draft" <?php echo ($country['status'] ?? '') === 'draft' ? 'selected' : ''; ?>>Draft</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Sort Order</label>
                                <input type="number" name="sort_order" value="<?php echo esc($country['sort_order'] ?? 0); ?>">
                            </div>
                            <div class="form-group">
                                <label>Hero Image</label>
                                <input type="file" name="hero_image" accept="image/*">
                                <?php if (!empty($country['image'])): ?>
                                <small>Current: <a href="<?php echo esc($country['image']); ?>" target="_blank"><?php echo esc($country['image']); ?></a></small>
                                <?php endif; ?>
                            </div>
                            <div class="form-group">
                                <label>Or Image URL</label>
                                <input type="text" name="image" value="<?php echo esc($country['image'] ?? ''); ?>">
                            </div>
                            <div class="form-group full">
                                <label>Description</label>
                                <textarea name="description" rows="4"><?php echo esc($country['description'] ?? ''); ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div style="display:flex;gap:1rem;margin-top:1rem;">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Country</button>
                    <a href="countries.php" class="btn btn-outline">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
