<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$filters = ['status' => $_GET['status'] ?? '', 'search' => $_GET['search'] ?? ''];
$tours = getTours(array_filter($filters));
$destinations = getDestinations();
$destMap = array_column($destinations, 'name', 'id');

// Handle quick toggles via AJAX-like POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['id'])) {
    csrf_verify();
    if ($_POST['action'] === 'toggle_status') {
        $newStatus = $_POST['value'] === 'active' ? 'active' : 'draft';
        $pdo->prepare("UPDATE tours SET status = ? WHERE id = ?")->execute([$newStatus, $_POST['id']]);
        echo 'OK'; exit;
    }
    if ($_POST['action'] === 'toggle_featured') {
        $newFeatured = $_POST['value'] === 'yes' ? 'yes' : 'no';
        $pdo->prepare("UPDATE tours SET featured = ? WHERE id = ?")->execute([$newFeatured, $_POST['id']]);
        echo 'OK'; exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    csrf_verify();
    $pdo->prepare("DELETE FROM tours WHERE id = ?")->execute([$_POST['id']]);
    flash('Tour deleted.');
    redirect('/admin/tours.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tour Packages | Bamba Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main">
        <div class="topbar">
            <h1>Tour Packages (<?php echo count($tours); ?>)</h1>
            <div class="topbar-right">
                <a href="/" target="_blank" class="btn btn-outline btn-sm"><i class="fas fa-external-link-alt"></i> View Site</a>
                <a href="tour-edit.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add Tour</a>
            </div>
        </div>
        <div class="content">
            <div class="toolbar">
                <form class="toolbar-search" method="get">
                    <input type="text" name="search" placeholder="Search tours..." value="<?php echo esc($_GET['search'] ?? ''); ?>">
                    <select name="status" style="padding:0.5rem;border:1.5px solid var(--border);border-radius:6px;font-size:0.85rem;">
                        <option value="">All Status</option>
                        <option value="active" <?php echo ($_GET['status'] ?? '') === 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="draft" <?php echo ($_GET['status'] ?? '') === 'draft' ? 'selected' : ''; ?>>Draft</option>
                    </select>
                    <button type="submit" class="btn btn-sm btn-outline"><i class="fas fa-search"></i></button>
                    <?php if ($_GET['search'] ?? '' || $_GET['status'] ?? ''): ?>
                        <a href="tours.php" class="btn btn-sm btn-outline"><i class="fas fa-times"></i> Clear</a>
                    <?php endif; ?>
                </form>
            </div>
            <div class="card">
                <div class="card-body" style="padding:0;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Title</th>
                                <th>Destination</th>
                                <th>Category</th>
                                <th>Duration</th>
                                <th>Price</th>
                                <th>Featured</th>
                                <th>Status</th>
                                <th style="width:140px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tours as $t): ?>
                            <tr data-id="<?php echo $t['id']; ?>">
                                <td>
                                    <?php $imgs = json_decode($t['images'] ?? '[]', true); ?>
                                    <img src="<?php echo esc($imgs[0] ?? 'https://placehold.co/60x40?text=No+Img'); ?>" alt="">
                                </td>
                                <td><strong><?php echo esc($t['title']); ?></strong></td>
                                <td><?php echo esc($destMap[$t['destination_id']] ?? 'N/A'); ?></td>
                                <td>
                                    <?php $tourCats = getTourCategoriesForTour($t['id']); ?>
                                    <?php foreach ($tourCats as $tc): ?>
                                    <span class="badge badge-published" style="margin-right:3px;margin-bottom:3px;display:inline-block;"><?php echo esc($tc['name']); ?></span>
                                    <?php endforeach; ?>
                                    <?php if (empty($tourCats)): ?><span class="badge badge-draft">Uncategorized</span><?php endif; ?>
                                </td>
                                <td><?php echo esc($t['duration']); ?></td>
                                <td><?php echo $t['price'] ? '$' . number_format($t['price']) : 'Contact'; ?></td>
                                <td>
                                    <div class="toggle <?php echo $t['featured'] === 'yes' ? 'on' : ''; ?>" onclick="toggleField(this, 'toggle_featured', <?php echo $t['id']; ?>)"></div>
                                </td>
                                <td>
                                    <div class="toggle <?php echo $t['status'] === 'active' ? 'on' : ''; ?>" onclick="toggleField(this, 'toggle_status', <?php echo $t['id']; ?>)"></div>
                                    <span class="badge badge-<?php echo $t['status'] === 'active' ? 'published' : 'draft'; ?>" style="margin-left:6px;"><?php echo ucfirst($t['status']); ?></span>
                                </td>
                                <td>
                                    <div class="actions">
                                        <a href="tour-edit.php?id=<?php echo $t['id']; ?>" class="action-btn edit" title="Edit"><i class="fas fa-pen"></i></a>
                                        <?php echo action_form('/admin/tours.php', ['action' => 'delete', 'id' => $t['id']], 'action-btn delete', 'fas fa-trash', 'Delete this tour?'); ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($tours)): ?>
                            <tr><td colspan="9" class="empty-state">No tours found. <a href="tour-edit.php">Add your first tour</a>.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="toast" id="toast"></div>
    <script>
    function toggleField(el, action, id) {
        el.classList.toggle('on');
        const value = el.classList.contains('on') ? (action === 'toggle_featured' ? 'yes' : 'active') : (action === 'toggle_featured' ? 'no' : 'draft');
        const form = new FormData();
            form.append('csrf_token', window.BAMBA_CSRF);
        form.append('action', action);
        form.append('id', id);
        form.append('value', value);
        fetch('tours.php', { method: 'POST', body: form })
            .then(r => r.text())
            .then(() => { showToast('Updated'); location.reload(); })
            .catch(() => showToast('Error'));
    }
    function showToast(msg) {
        const t = document.getElementById('toast');
        t.textContent = msg; t.classList.add('show');
        setTimeout(() => t.classList.remove('show'), 2500);
    }
    </script>
</body>
</html>
