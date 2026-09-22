<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$message = $_SESSION['flash_message'] ?? '';
unset($_SESSION['flash_message']);

$pages = getPages('active');
$draftPages = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM pages WHERE status = 'draft' ORDER BY sort_order, title");
    $stmt->execute();
    $draftPages = $stmt->fetchAll();
} catch (Exception $e) {
    $draftPages = [];
}

$allPages = array_merge($pages, $draftPages);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    csrf_verify();
    $id = (int) ($_POST['id'] ?? 0);
    try {
        $pdo->prepare("DELETE FROM pages WHERE id = ?")->execute([$id]);
        flash('Page deleted.');
    } catch (Exception $e) {
        flash('Could not delete that page.', 'error');
    }
    redirect('/admin/pages.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'toggle') {
    csrf_verify();
    $id = (int) ($_POST['id'] ?? 0);
    try {
        $pdo->prepare("UPDATE pages SET status = IF(status='active','draft','active') WHERE id = ?")->execute([$id]);
        flash('Page status changed.');
    } catch (Exception $e) {
        flash('Could not change that page.', 'error');
    }
    redirect('/admin/pages.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pages | Bamba Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main">
        <div class="topbar">
            <h1>Pages</h1>
            <a href="/admin/page-edit.php" class="btn btn-primary"><i class="fas fa-plus"></i> New Page</a>
        </div>
        <div class="content">
            <?php if ($message): ?>
            <div class="alert alert-success"><?php echo esc($message); ?></div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h2>All Pages</h2>
                </div>
                <div class="card-body" style="padding:0;">
                    <table class="data-table">
                        <thead>
                            <tr><th>Title</th><th>Slug</th><th>Nav</th><th>Status</th><th>Last Updated</th><th>Actions</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($allPages as $p): ?>
                            <tr>
                                <td><strong><?php echo esc($p['title']); ?></strong></td>
                                <td><code>/page/<?php echo esc($p['slug']); ?></code></td>
                                <td><?php echo $p['show_in_nav'] === 'yes' ? '<span class="badge badge-active">Yes</span>' : '<span class="badge">No</span>'; ?></td>
                                <td><span class="badge badge-<?php echo $p['status']; ?>"><?php echo ucfirst($p['status']); ?></span></td>
                                <td><?php echo date('M j, Y', strtotime($p['updated_at'])); ?></td>
                                <td>
                                    <a href="/admin/page-edit.php?id=<?php echo $p['id']; ?>" class="btn btn-sm btn-outline" title="Edit"><i class="fas fa-edit"></i></a>
                                    <a href="/page/<?php echo esc($p['slug']); ?>" target="_blank" class="btn btn-sm btn-outline" title="View"><i class="fas fa-eye"></i></a>
                    <?php echo action_form('/admin/pages.php', ['action' => 'toggle', 'id' => $p['id']], 'btn btn-sm btn-outline', $p['status'] === 'active' ? 'fas fa-toggle-on' : 'fas fa-toggle-off', '', 'Toggle status'); ?>
                                    <?php echo action_form('/admin/pages.php', ['action' => 'delete', 'id' => $p['id']], 'btn btn-sm btn-danger', 'fas fa-trash', 'Delete this page?'); ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($allPages)): ?>
                            <tr><td colspan="6" style="text-align:center;padding:2rem;color:var(--text-light);">No pages yet. <a href="/admin/page-edit.php">Create your first page</a>.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
