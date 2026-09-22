<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$careers = getCareers();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    csrf_verify();
    $pdo->prepare("DELETE FROM careers WHERE id = ?")->execute([$_POST['id']]);
    flash('Career listing deleted.');
    redirect('/admin/careers.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Careers | Bamba Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main">
        <div class="topbar">
            <h1>Careers</h1>
            <div class="topbar-right">
                <a href="career-edit.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add Job</a>
            </div>
        </div>
        <div class="content">
            <div class="card">
                <div class="card-body" style="padding:0;">
                    <table class="data-table">
                        <thead>
                            <tr><th>Title</th><th>Department</th><th>Location</th><th>Type</th><th>Status</th><th>Actions</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($careers as $c): ?>
                            <tr>
                                <td><strong><?php echo esc($c['title']); ?></strong></td>
                                <td><?php echo esc($c['department'] ?? ''); ?></td>
                                <td><?php echo esc($c['location'] ?? ''); ?></td>
                                <td><?php echo esc($c['job_type'] ?? ''); ?></td>
                                <td><span class="badge badge-<?php echo $c['status'] === 'active' ? 'active' : 'draft'; ?>"><?php echo ucfirst($c['status']); ?></span></td>
                                <td>
                                    <a href="career-edit.php?id=<?php echo $c['id']; ?>" class="btn btn-sm btn-outline"><i class="fas fa-pen"></i></a>
                                    <?php echo action_form('/admin/careers.php', ['action' => 'delete', 'id' => $c['id']], 'btn btn-sm btn-danger', 'fas fa-trash', 'Delete?'); ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($careers)): ?>
                            <tr><td colspan="6" style="text-align:center;padding:3rem;color:var(--text-light);">No jobs found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
