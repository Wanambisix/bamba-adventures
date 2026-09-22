<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$category = null;
$errors = [];

if (!empty($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM tour_categories WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $category = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $data = [
        'name' => trim($_POST['name'] ?? ''),
        'slug' => slugify(trim($_POST['slug'] ?? $_POST['name'] ?? '')),
        'description' => trim($_POST['description'] ?? ''),
        'status' => $_POST['status'] ?? 'active',
        'sort_order' => intval($_POST['sort_order'] ?? 0),
    ];
    
    if (empty($data['name'])) $errors[] = 'Name is required';
    
    if (empty($errors)) {
        if ($category) {
            $sql = "UPDATE tour_categories SET name=:name, slug=:slug, description=:description, status=:status, sort_order=:sort_order WHERE id=:id";
            $data['id'] = $category['id'];
            $stmt = $pdo->prepare($sql);
            $stmt->execute($data);
        } else {
            $sql = "INSERT INTO tour_categories (name, slug, description, status, sort_order) VALUES (:name, :slug, :description, :status, :sort_order)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($data);
        }
        header('Location: /admin/categories.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $category ? 'Edit' : 'Add'; ?> Category | Bamba Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main">
        <div class="topbar">
            <h1><?php echo $category ? 'Edit' : 'Add'; ?> Category</h1>
            <a href="categories.php" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Back</a>
        </div>
        <div class="content">
            <?php if ($errors): ?>
            <div class="alert alert-error"><?php echo implode('<br>', $errors); ?></div>
            <?php endif; ?>
            <form method="POST">
            <?php echo csrf_field(); ?>
                <div class="card">
                    <div class="card-header"><h2>Category Details</h2></div>
                    <div class="card-body">
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Name *</label>
                                <input type="text" name="name" value="<?php echo esc($category['name'] ?? ''); ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Slug</label>
                                <input type="text" name="slug" value="<?php echo esc($category['slug'] ?? ''); ?>" placeholder="auto-generated if empty">
                            </div>
                            <div class="form-group">
                                <label>Status</label>
                                <select name="status">
                                    <option value="active" <?php echo ($category['status'] ?? '') === 'active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="draft" <?php echo ($category['status'] ?? '') === 'draft' ? 'selected' : ''; ?>>Draft</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Sort Order</label>
                                <input type="number" name="sort_order" value="<?php echo esc($category['sort_order'] ?? 0); ?>">
                            </div>
                            <div class="form-group full">
                                <label>Description</label>
                                <textarea name="description" rows="3"><?php echo esc($category['description'] ?? ''); ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div style="display:flex;gap:1rem;margin-top:1rem;">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Category</button>
                    <a href="categories.php" class="btn btn-outline">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
