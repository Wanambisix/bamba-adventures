<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$testimonials = getTestimonials();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    csrf_verify();
    $pdo->prepare("DELETE FROM testimonials WHERE id = ?")->execute([$_POST['id']]);
    flash('Testimonial deleted.');
    redirect('/admin/testimonials.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Testimonials | Bamba Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main">
        <div class="topbar">
            <h1>Testimonials</h1>
            <div class="topbar-right">
                <a href="testimonial-edit.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add Testimonial</a>
            </div>
        </div>
        <div class="content">
            <div class="card">
                <div class="card-body" style="padding:0;">
                    <table class="data-table">
                        <thead>
                            <tr><th>Name</th><th>Role</th><th>Text</th><th>Rating</th><th>Status</th><th>Actions</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($testimonials as $t): ?>
                            <tr>
                                <td><strong><?php echo esc($t['name']); ?></strong></td>
                                <td><?php echo esc($t['role'] ?? ''); ?></td>
                                <td><?php echo esc(excerpt($t['text'], 60)); ?></td>
                                <td><?php echo $t['rating']; ?>/5</td>
                                <td><span class="badge badge-<?php echo $t['status'] === 'active' ? 'active' : 'draft'; ?>"><?php echo ucfirst($t['status']); ?></span></td>
                                <td>
                                    <a href="testimonial-edit.php?id=<?php echo $t['id']; ?>" class="btn btn-sm btn-outline"><i class="fas fa-pen"></i></a>
                                    <?php echo action_form('/admin/testimonials.php', ['action' => 'delete', 'id' => $t['id']], 'btn btn-sm btn-danger', 'fas fa-trash', 'Delete?'); ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($testimonials)): ?>
                            <tr><td colspan="6" style="text-align:center;padding:3rem;color:var(--text-light);">No testimonials found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
