<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$subscribers = $pdo->query("SELECT * FROM subscribers ORDER BY created_at DESC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    csrf_verify();
    $pdo->prepare("DELETE FROM subscribers WHERE id = ?")->execute([$_POST['id']]);
    flash('Subscriber deleted.');
    redirect('/admin/subscribers.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Subscribers | Bamba Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main">
        <div class="topbar">
            <h1>Subscribers</h1>
            <div class="topbar-right">
                <span class="btn btn-outline" style="cursor:default;"><i class="fas fa-users"></i> <?php echo count($subscribers); ?> Total</span>
            </div>
        </div>
        <div class="content">
            <div class="card">
                <div class="card-body" style="padding:0;">
                    <table class="data-table">
                        <thead>
                            <tr><th>Email</th><th>Status</th><th>Date</th><th>Actions</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($subscribers as $s): ?>
                            <tr>
                                <td><?php echo esc($s['email']); ?></td>
                                <td><span class="badge badge-<?php echo $s['status'] === 'active' ? 'active' : 'draft'; ?>"><?php echo ucfirst($s['status']); ?></span></td>
                                <td><?php echo date('M j, Y', strtotime($s['created_at'])); ?></td>
                                <td>
                                    <?php echo action_form('/admin/subscribers.php', ['action' => 'delete', 'id' => $s['id']], 'btn btn-sm btn-danger', 'fas fa-trash', 'Delete?'); ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($subscribers)): ?>
                            <tr><td colspan="4" style="text-align:center;padding:3rem;color:var(--text-light);">No subscribers yet.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
