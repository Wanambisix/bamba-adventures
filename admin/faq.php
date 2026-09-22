<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$faqs = getFaqs();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    csrf_verify();
    $pdo->prepare("DELETE FROM faqs WHERE id = ?")->execute([$_POST['id']]);
    flash('FAQ deleted.');
    redirect('/admin/faq.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>FAQ | Bamba Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main">
        <div class="topbar">
            <h1>FAQs</h1>
            <div class="topbar-right">
                <a href="faq-edit.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add FAQ</a>
            </div>
        </div>
        <div class="content">
            <div class="card">
                <div class="card-body" style="padding:0;">
                    <table class="data-table">
                        <thead>
                            <tr><th>Question</th><th>Category</th><th>Status</th><th>Actions</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($faqs as $f): ?>
                            <tr>
                                <td><?php echo esc(excerpt($f['question'], 80)); ?></td>
                                <td><?php echo esc($f['category'] ?? ''); ?></td>
                                <td><span class="badge badge-<?php echo $f['status'] === 'active' ? 'active' : 'draft'; ?>"><?php echo ucfirst($f['status']); ?></span></td>
                                <td>
                                    <a href="faq-edit.php?id=<?php echo $f['id']; ?>" class="btn btn-sm btn-outline"><i class="fas fa-pen"></i></a>
                                    <?php echo action_form('/admin/faq.php', ['action' => 'delete', 'id' => $f['id']], 'btn btn-sm btn-danger', 'fas fa-trash', 'Delete?'); ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($faqs)): ?>
                            <tr><td colspan="4" style="text-align:center;padding:3rem;color:var(--text-light);">No FAQs found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
