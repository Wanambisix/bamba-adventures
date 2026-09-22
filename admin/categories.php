<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$categories = getTourCategories();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['id'])) {
    csrf_verify();
    if ($_POST['action'] === 'toggle_status') {
        $newStatus = $_POST['value'];
        $pdo->prepare("UPDATE tour_categories SET status = ? WHERE id = ?")->execute([$newStatus, $_POST['id']]);
        echo 'OK'; exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    csrf_verify();
    $pdo->prepare("DELETE FROM tour_categories WHERE id = ?")->execute([$_POST['id']]);
    flash('Category deleted.');
    redirect('/admin/categories.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tour Categories | Bamba Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main">
        <div class="topbar">
            <h1>Tour Categories (<?php echo count($categories); ?>)</h1>
            <div class="topbar-right">
                <a href="category-edit.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add Category</a>
            </div>
        </div>
        <div class="content">
            <div class="card">
                <div class="card-body" style="padding:0;">
                    <table class="data-table">
                        <thead>
                            <tr><th>Name</th><th>Slug</th><th>Description</th><th>Status</th><th style="width:120px;">Actions</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $c): ?>
                            <tr data-id="<?php echo $c['id']; ?>">
                                <td><strong><?php echo esc($c['name']); ?></strong></td>
                                <td><?php echo esc($c['slug']); ?></td>
                                <td><?php echo esc($c['description'] ?: '-'); ?></td>
                                <td>
                                    <div class="toggle <?php echo $c['status'] === 'active' ? 'on' : ''; ?>" onclick="toggleField(this, <?php echo $c['id']; ?>)"></div>
                                    <span class="badge badge-<?php echo $c['status'] === 'active' ? 'published' : 'draft'; ?>" style="margin-left:6px;"><?php echo ucfirst($c['status']); ?></span>
                                </td>
                                <td>
                                    <div class="actions">
                                        <a href="category-edit.php?id=<?php echo $c['id']; ?>" class="action-btn edit"><i class="fas fa-pen"></i></a>
                                        <?php echo action_form('/admin/categories.php', ['action' => 'delete', 'id' => $c['id']], 'action-btn delete', 'fas fa-trash', 'Delete this category? Tours linked to it will lose this assignment.'); ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($categories)): ?>
                            <tr><td colspan="5" class="empty-state">No categories. <a href="category-edit.php">Add your first category</a>.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="toast" id="toast"></div>
    <script>
    function toggleField(el, id) {
        el.classList.toggle('on');
        const value = el.classList.contains('on') ? 'active' : 'draft';
        const form = new FormData();
            form.append('csrf_token', window.BAMBA_CSRF);
        form.append('action', 'toggle_status');
        form.append('id', id);
        form.append('value', value);
        fetch('categories.php', { method: 'POST', body: form })
            .then(() => { showToast('Updated'); location.reload(); });
    }
    function showToast(msg) {
        const t = document.getElementById('toast');
        t.textContent = msg; t.classList.add('show');
        setTimeout(() => t.classList.remove('show'), 2500);
    }
    </script>
</body>
</html>
