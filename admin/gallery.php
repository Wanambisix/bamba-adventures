<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$images = $pdo->query("SELECT * FROM gallery ORDER BY uploaded_at DESC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    csrf_verify();
    $pdo->prepare("DELETE FROM gallery WHERE id = ?")->execute([$_POST['id']]);
    flash('Image deleted.');
    redirect('/admin/gallery.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['filename'])) {
    csrf_verify();
    $pdo->prepare("INSERT INTO gallery (filename, caption, category) VALUES (?, ?, ?)")
        ->execute([$_POST['filename'], $_POST['caption'] ?? '', $_POST['category'] ?? '']);
    header('Location: /admin/gallery.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Gallery | Bamba Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main">
        <div class="topbar">
            <h1>Gallery</h1>
        </div>
        <div class="content">
            <div class="card">
                <div class="card-header"><h2>Add Image</h2></div>
                <div class="card-body">
                    <form method="POST" class="form-grid">
            <?php echo csrf_field(); ?>
                        <div class="form-group">
                            <label>Image URL</label>
                            <input type="text" name="filename" placeholder="https://..." required>
                        </div>
                        <div class="form-group">
                            <label>Caption</label>
                            <input type="text" name="caption">
                        </div>
                        <div class="form-group">
                            <label>Category</label>
                            <input type="text" name="category" placeholder="e.g. Safari, Beach">
                        </div>
                        <div class="form-group" style="display:flex;align-items:flex-end;">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Add</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="card">
                <div class="card-body" style="padding:0;">
                    <table class="data-table">
                        <thead>
                            <tr><th>Image</th><th>Caption</th><th>Category</th><th>Date</th><th>Actions</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($images as $img): ?>
                            <tr>
                                <td><img src="<?php echo esc($img['filename']); ?>" alt="" style="max-width:100px;"></td>
                                <td><?php echo esc($img['caption'] ?? ''); ?></td>
                                <td><?php echo esc($img['category'] ?? ''); ?></td>
                                <td><?php echo date('M j, Y', strtotime($img['uploaded_at'])); ?></td>
                                <td>
                                    <?php echo action_form('/admin/gallery.php', ['action' => 'delete', 'id' => $img['id']], 'btn btn-sm btn-danger', 'fas fa-trash', 'Delete?'); ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($images)): ?>
                            <tr><td colspan="5" style="text-align:center;padding:3rem;color:var(--text-light);">No images in gallery.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
