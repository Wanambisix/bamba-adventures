<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update') {
    csrf_verify();
    $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?")->execute([$_POST['status'], $_POST['id']]);
    flash('Booking marked as ' . $_POST['status'] . '.');
    redirect('/admin/bookings.php');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    csrf_verify();
    $pdo->prepare("DELETE FROM bookings WHERE id = ?")->execute([$_POST['id']]);
    flash('Booking deleted.');
    redirect('/admin/bookings.php');
}

$statusFilter = $_GET['status'] ?? '';
$sql = "SELECT b.*, t.title as tour_title FROM bookings b LEFT JOIN tours t ON b.tour_id = t.id WHERE 1=1";
$params = [];
if ($statusFilter) { $sql .= " AND b.status = ?"; $params[] = $statusFilter; }
$sql .= " ORDER BY b.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$bookings = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Bookings | Bamba Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main">
        <div class="topbar"><h1>Bookings</h1></div>
        <div class="content">
            <div class="toolbar">
                <form method="get" class="toolbar-search">
                    <select name="status" style="padding:0.5rem;border:1.5px solid var(--border);border-radius:6px;">
                        <option value="">All Status</option>
                        <option value="new" <?php echo $statusFilter==='new'?'selected':''; ?>>New</option>
                        <option value="contacted" <?php echo $statusFilter==='contacted'?'selected':''; ?>>Contacted</option>
                        <option value="confirmed" <?php echo $statusFilter==='confirmed'?'selected':''; ?>>Confirmed</option>
                        <option value="cancelled" <?php echo $statusFilter==='cancelled'?'selected':''; ?>>Cancelled</option>
                    </select>
                    <button type="submit" class="btn btn-sm btn-outline">Filter</button>
                </form>
            </div>
            <div class="card">
                <div class="card-body" style="padding:0;">
                    <table class="data-table">
                        <thead>
                            <tr><th>Name</th><th>Email</th><th>Phone</th><th>Tour</th><th>Travel Date</th><th>Guests</th><th>Status</th><th>Date</th><th>Actions</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bookings as $b): ?>
                            <tr>
                                <td><strong><?php echo esc($b['name']); ?></strong></td>
                                <td><?php echo esc($b['email']); ?></td>
                                <td><?php echo esc($b['phone']); ?></td>
                                <td><?php echo esc($b['tour_title'] ?? 'N/A'); ?></td>
                                <td><?php echo $b['travel_date'] ? date('M j, Y', strtotime($b['travel_date'])) : '-'; ?></td>
                                <td><?php echo ($b['adults']??0) + ($b['children']??0); ?></td>
                                <td><span class="badge badge-<?php echo $b['status']; ?>"><?php echo ucfirst($b['status']); ?></span></td>
                                <td><?php echo date('M j', strtotime($b['created_at'])); ?></td>
                                <td>
                                    <?php if ($b['status'] === 'new'): ?>
                                    <?php echo action_form('/admin/bookings.php', ['action' => 'update', 'id' => $b['id'], 'status' => 'contacted'], 'btn btn-sm btn-outline', 'fas fa-phone', ''); ?>
                                    <?php endif; ?>
                                    <?php echo action_form('/admin/bookings.php', ['action' => 'delete', 'id' => $b['id']], 'btn btn-sm btn-danger', 'fas fa-trash', 'Delete?'); ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($bookings)): ?>
                            <tr><td colspan="9" style="text-align:center;padding:3rem;color:var(--text-light);">No bookings found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
