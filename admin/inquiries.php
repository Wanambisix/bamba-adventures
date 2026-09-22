<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$statusFilter = $_GET['status'] ?? '';
$sql = "SELECT * FROM inquiries WHERE 1=1";
$params = [];
if ($statusFilter) { $sql .= " AND status = ?"; $params[] = $statusFilter; }
$sql .= " ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$inquiries = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    csrf_verify();
    $pdo->prepare("DELETE FROM inquiries WHERE id = ?")->execute([$_POST['id']]);
    flash('Inquiry deleted.');
    redirect('/admin/inquiries.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'mark') {
    csrf_verify();
    $pdo->prepare("UPDATE inquiries SET status = ? WHERE id = ?")->execute([$_POST['mark'], $_POST['id']]);
    flash('Inquiry marked as ' . $_POST['mark'] . '.');
    redirect('/admin/inquiries.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Inquiries | Bamba Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main">
        <div class="topbar">
            <h1>Inquiries</h1>
        </div>
        <div class="content">
            <div class="toolbar">
                <form class="toolbar-search" method="get">
                    <select name="status" style="padding:0.5rem;border:1.5px solid var(--border);border-radius:6px;font-size:0.85rem;" onchange="this.form.submit()">
                        <option value="">All Status</option>
                        <option value="new" <?php echo $statusFilter === 'new' ? 'selected' : ''; ?>>New</option>
                        <option value="read" <?php echo $statusFilter === 'read' ? 'selected' : ''; ?>>Read</option>
                        <option value="replied" <?php echo $statusFilter === 'replied' ? 'selected' : ''; ?>>Replied</option>
                    </select>
                </form>
            </div>
            <div class="card">
                <div class="card-body" style="padding:0;">
                    <table class="data-table">
                        <thead>
                            <tr><th>Name</th><th>Email</th><th>Subject</th><th>Message</th><th>Status</th><th>Date</th><th>Actions</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($inquiries as $i): ?>
                            <tr>
                                <td><?php echo esc($i['name']); ?></td>
                                <td><a href="mailto:<?php echo esc($i['email']); ?>"><?php echo esc($i['email']); ?></a></td>
                                <td><?php echo esc($i['subject'] ?? 'General'); ?></td>
                                <td><?php echo esc(excerpt($i['message'], 80)); ?></td>
                                <td><span class="badge badge-<?php echo $i['status']; ?>"><?php echo ucfirst($i['status']); ?></span></td>
                                <td><?php echo date('M j, Y', strtotime($i['created_at'])); ?></td>
                                <td>
                                    <?php if ($i['status'] === 'new'): ?>
                                    <?php echo action_form('/admin/inquiries.php', ['action' => 'mark', 'id' => $i['id'], 'mark' => 'read'], 'btn btn-sm btn-success', 'fas fa-check', ''); ?>
                                    <?php endif; ?>
                                    <?php echo action_form('/admin/inquiries.php', ['action' => 'delete', 'id' => $i['id']], 'btn btn-sm btn-danger', 'fas fa-trash', 'Delete?'); ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($inquiries)): ?>
                            <tr><td colspan="7" style="text-align:center;padding:3rem;color:var(--text-light);">No inquiries found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
