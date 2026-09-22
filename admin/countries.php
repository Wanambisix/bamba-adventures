<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$destinations = getDestinations();
$destMap = array_column($destinations, 'name', 'id');

$filters = ['destination_id' => $_GET['destination_id'] ?? ''];
$countries = [];

if (!empty($filters['destination_id'])) {
    $countries = getCountries($filters['destination_id']);
} else {
    $countries = getCountries();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['id'])) {
    csrf_verify();
    if ($_POST['action'] === 'toggle_status') {
        $newStatus = $_POST['value'];
        $pdo->prepare("UPDATE countries SET status = ? WHERE id = ?")->execute([$newStatus, $_POST['id']]);
        echo 'OK'; exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    csrf_verify();
    $pdo->prepare("DELETE FROM countries WHERE id = ?")->execute([$_POST['id']]);
    flash('Country deleted.');
    redirect('/admin/countries.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Countries | Bamba Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main">
        <div class="topbar">
            <h1>Countries (<?php echo count($countries); ?>)</h1>
            <div class="topbar-right">
                <a href="country-edit.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add Country</a>
            </div>
        </div>
        <div class="content">
            <div class="toolbar">
                <form class="toolbar-search" method="get">
                    <select name="destination_id" style="padding:0.5rem;border:1.5px solid var(--border);border-radius:6px;font-size:0.85rem;">
                        <option value="">All Destinations</option>
                        <?php foreach ($destinations as $d): ?>
                        <option value="<?php echo $d['id']; ?>" <?php echo ($_GET['destination_id'] ?? '') == $d['id'] ? 'selected' : ''; ?>><?php echo esc($d['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-sm btn-outline"><i class="fas fa-search"></i></button>
                    <?php if ($_GET['destination_id'] ?? ''): ?>
                        <a href="countries.php" class="btn btn-sm btn-outline"><i class="fas fa-times"></i> Clear</a>
                    <?php endif; ?>
                </form>
            </div>
            <div class="card">
                <div class="card-body" style="padding:0;">
                    <table class="data-table">
                        <thead>
                            <tr><th>Image</th><th>Name</th><th>Destination</th><th>Slug</th><th>Status</th><th style="width:120px;">Actions</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($countries as $c): ?>
                            <tr data-id="<?php echo $c['id']; ?>">
                                <td>
                                    <?php if ($c['image']): ?>
                                        <img src="<?php echo esc($c['image']); ?>" alt="" style="width:60px;height:40px;object-fit:cover;border-radius:6px;">
                                    <?php else: ?>
                                        <i class="fas fa-flag" style="font-size:1.5rem;color:var(--primary);"></i>
                                    <?php endif; ?>
                                </td>
                                <td><strong><?php echo esc($c['name']); ?></strong></td>
                                <td><?php echo esc($destMap[$c['destination_id']] ?? 'N/A'); ?></td>
                                <td><?php echo esc($c['slug']); ?></td>
                                <td>
                                    <div class="toggle <?php echo $c['status'] === 'active' ? 'on' : ''; ?>" onclick="toggleField(this, <?php echo $c['id']; ?>)"></div>
                                    <span class="badge badge-<?php echo $c['status'] === 'active' ? 'published' : 'draft'; ?>" style="margin-left:6px;"><?php echo ucfirst($c['status']); ?></span>
                                </td>
                                <td>
                                    <div class="actions">
                                        <a href="country-edit.php?id=<?php echo $c['id']; ?>" class="action-btn edit"><i class="fas fa-pen"></i></a>
                                        <?php echo action_form('/admin/countries.php', ['action' => 'delete', 'id' => $c['id']], 'action-btn delete', 'fas fa-trash', 'Delete this country?'); ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($countries)): ?>
                            <tr><td colspan="6" class="empty-state">No countries found. <a href="country-edit.php">Add your first country</a>.</td></tr>
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
        fetch('countries.php', { method: 'POST', body: form })
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
