<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$destinations = getDestinations();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['id'])) {
    csrf_verify();
    if ($_POST['action'] === 'toggle_status') {
        $newStatus = $_POST['value'];
        $pdo->prepare("UPDATE destinations SET status = ? WHERE id = ?")->execute([$newStatus, $_POST['id']]);
        echo 'OK'; exit;
    }
    if ($_POST['action'] === 'toggle_featured') {
        $newFeatured = $_POST['value'];
        $pdo->prepare("UPDATE destinations SET featured = ? WHERE id = ?")->execute([$newFeatured, $_POST['id']]);
        echo 'OK'; exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    csrf_verify();
    $pdo->prepare("DELETE FROM destinations WHERE id = ?")->execute([$_POST['id']]);
    flash('Destination deleted.');
    redirect('/admin/destinations.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Destinations | Bamba Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main">
        <div class="topbar">
            <h1>Destinations (<?php echo count($destinations); ?>)</h1>
            <div class="topbar-right">
                <a href="destination-edit.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add Destination</a>
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
                            <?php foreach ($destinations as $d): ?>
                            <tr data-id="<?php echo $d['id']; ?>">
                                <td><img src="<?php echo esc($d['image'] ?? 'https://placehold.co/60x40?text=No+Img'); ?>" alt=""></td>
                                <td><strong><?php echo esc($d['name']); ?></strong></td>
                                <td><?php echo esc($d['slug']); ?></td>
                                <td>
                                    <div class="toggle <?php echo $d['featured'] === 'yes' ? 'on' : ''; ?>" onclick="toggleFeatured(this, <?php echo $d['id']; ?>)"></div>
                                    <span class="badge badge-<?php echo $d['featured'] === 'yes' ? 'published' : 'draft'; ?>" style="margin-left:6px;"><?php echo $d['featured'] === 'yes' ? 'Yes' : 'No'; ?></span>
                                </td>
                                <td>
                                    <div class="toggle <?php echo $d['status'] === 'active' ? 'on' : ''; ?>" onclick="toggleStatus(this, <?php echo $d['id']; ?>)"></div>
                                    <span class="badge badge-<?php echo $d['status'] === 'active' ? 'published' : 'draft'; ?>" style="margin-left:6px;"><?php echo ucfirst($d['status']); ?></span>
                                </td>
                                <td>
                                    <div class="actions">
                                        <a href="destination-edit.php?id=<?php echo $d['id']; ?>" class="action-btn edit"><i class="fas fa-pen"></i></a>
                                        <?php echo action_form('/admin/destinations.php', ['action' => 'delete', 'id' => $d['id']], 'action-btn delete', 'fas fa-trash', 'Delete?'); ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($destinations)): ?>
                            <tr><td colspan="6" class="empty-state">No destinations. <a href="destination-edit.php">Add one</a>.</td></tr>
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
        fetch('destinations.php', { method: 'POST', body: form })
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
        fetch('destinations.php', { method: 'POST', body: form })
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
