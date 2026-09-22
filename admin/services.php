<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$services = getServices();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['id'])) {
    csrf_verify();
    if ($_POST['action'] === 'toggle_status') {
        $newStatus = $_POST['value'];
        $pdo->prepare("UPDATE services SET status = ? WHERE id = ?")->execute([$newStatus, $_POST['id']]);
        echo 'OK'; exit;
    }
    if ($_POST['action'] === 'toggle_featured') {
        $newFeatured = $_POST['value'];
        $pdo->prepare("UPDATE services SET featured = ? WHERE id = ?")->execute([$newFeatured, $_POST['id']]);
        echo 'OK'; exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    csrf_verify();
    $pdo->prepare("DELETE FROM services WHERE id = ?")->execute([$_POST['id']]);
    flash('Service deleted.');
    redirect('/admin/services.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Services | Bamba Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main">
        <div class="topbar">
            <h1>Services (<?php echo count($services); ?>)</h1>
            <div class="topbar-right">
                <a href="service-edit.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add Service</a>
            </div>
        </div>
        <div class="content">
            <div class="card">
                <div class="card-body" style="padding:0;">
                    <table class="data-table">
                        <thead>
                            <tr><th>Image</th><th>Name</th><th>Slug</th><th>Featured</th><th>Status</th><th style="width:120px;">Actions</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($services as $s): ?>
                            <tr data-id="<?php echo $s['id']; ?>">
                                <td>
                                    <?php if ($s['image']): ?>
                                        <img src="<?php echo esc($s['image']); ?>" alt="" style="width:60px;height:40px;object-fit:cover;border-radius:6px;">
                                    <?php else: ?>
                                        <i class="<?php echo esc($s['icon']); ?>" style="font-size:1.5rem;color:var(--primary);"></i>
                                    <?php endif; ?>
                                </td>
                                <td><strong><?php echo esc($s['name']); ?></strong></td>
                                <td><?php echo esc($s['slug']); ?></td>
                                <td>
                                    <div class="toggle <?php echo $s['featured'] === 'yes' ? 'on' : ''; ?>" onclick="toggleFeatured(this, <?php echo $s['id']; ?>)"></div>
                                    <span class="badge badge-<?php echo $s['featured'] === 'yes' ? 'published' : 'draft'; ?>" style="margin-left:6px;"><?php echo $s['featured'] === 'yes' ? 'Yes' : 'No'; ?></span>
                                </td>
                                <td>
                                    <div class="toggle <?php echo $s['status'] === 'active' ? 'on' : ''; ?>" onclick="toggleStatus(this, <?php echo $s['id']; ?>)"></div>
                                    <span class="badge badge-<?php echo $s['status'] === 'active' ? 'published' : 'draft'; ?>" style="margin-left:6px;"><?php echo ucfirst($s['status']); ?></span>
                                </td>
                                <td>
                                    <div class="actions">
                                        <a href="service-edit.php?id=<?php echo $s['id']; ?>" class="action-btn edit"><i class="fas fa-pen"></i></a>
                                        <?php echo action_form('/admin/services.php', ['action' => 'delete', 'id' => $s['id']], 'action-btn delete', 'fas fa-trash', 'Delete?'); ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($services)): ?>
                            <tr><td colspan="6" class="empty-state">No services. <a href="service-edit.php">Add one</a>.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="toast" id="toast"></div>
    <script>
    function toggleStatus(el, id) {
        el.classList.toggle('on');
        const value = el.classList.contains('on') ? 'active' : 'draft';
        const form = new FormData();
            form.append('csrf_token', window.BAMBA_CSRF);
        form.append('action', 'toggle_status');
        form.append('id', id);
        form.append('value', value);
        fetch('services.php', { method: 'POST', body: form })
            .then(() => { showToast('Updated'); location.reload(); });
    }
    function toggleFeatured(el, id) {
        el.classList.toggle('on');
        const value = el.classList.contains('on') ? 'yes' : 'no';
        const form = new FormData();
            form.append('csrf_token', window.BAMBA_CSRF);
        form.append('action', 'toggle_featured');
        form.append('id', id);
        form.append('value', value);
        fetch('services.php', { method: 'POST', body: form })
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
