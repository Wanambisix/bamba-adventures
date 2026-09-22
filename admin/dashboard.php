<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

// Check if required tables exist
$requiredTables = ['tours', 'destinations', 'bookings', 'inquiries', 'subscribers', 'blog_posts'];
$missingTables = [];
try {
    $existingTables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($requiredTables as $table) {
        if (!in_array($table, $existingTables)) {
            $missingTables[] = $table;
        }
    }
} catch (PDOException $e) {
    $missingTables = $requiredTables;
}

$counts = [];
$recentBookings = [];
$recentInquiries = [];

if (empty($missingTables)) {
    $counts = getCounts();
    try {
        $recentBookings = $pdo->query("SELECT b.*, t.title as tour_title FROM bookings b LEFT JOIN tours t ON b.tour_id = t.id ORDER BY b.created_at DESC LIMIT 5")->fetchAll();
    } catch (PDOException $e) {
        $recentBookings = [];
    }
    try {
        $recentInquiries = $pdo->query("SELECT * FROM inquiries ORDER BY created_at DESC LIMIT 5")->fetchAll();
    } catch (PDOException $e) {
        $recentInquiries = [];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Bamba Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
    <style>
        .setup-box { background: #fffbeb; border: 1px solid #fcd34d; border-radius: 12px; padding: 2rem; margin: 2rem; }
        .setup-box h2 { color: #92400e; margin-bottom: 1rem; font-size: 1.2rem; }
        .setup-box p { color: #78350f; line-height: 1.7; margin-bottom: 1rem; }
        .setup-box ol { color: #78350f; line-height: 1.8; margin-left: 1.2rem; }
        .setup-box li { margin-bottom: 0.5rem; }
        .setup-box code { background: #fef3c7; padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.9rem; }
        .setup-box .btn { display: inline-block; background: #92400e; color: #fff; padding: 0.7rem 1.5rem; border-radius: 8px; text-decoration: none; font-weight: 600; margin-top: 0.5rem; }
        .setup-box .btn:hover { background: #78350f; }
        .missing-list { background: #fef3c7; padding: 1rem; border-radius: 8px; margin: 1rem 0; }
        .missing-list code { font-weight: 600; }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main">
        <div class="topbar">
            <h1>Dashboard</h1>
            <div class="topbar-right">
                <div class="topbar-user">
                    <i class="fas fa-user-circle"></i>
                    <?php echo esc($_SESSION['admin_name'] ?? 'Admin'); ?>
                </div>
            </div>
        </div>
        <div class="content">
            <?php if (!empty($missingTables)): ?>
            <div class="setup-box">
                <h2><i class="fas fa-exclamation-triangle"></i> Database Setup Required</h2>
                <p>The admin panel is connected to your database, but the required tables have not been created yet.</p>
                <div class="missing-list">
                    <strong>Missing tables:</strong> <?php foreach ($missingTables as $t): ?><code><?php echo $t; ?></code> <?php endforeach; ?>
                </div>
                <p><strong>To fix this, import the database schema:</strong></p>
                <ol>
                    <li>Log in to your cPanel and open <strong>phpMyAdmin</strong></li>
                    <li>Select your database (e.g., <code>bamba_db</code>)</li>
                    <li>Click the <strong>Import</strong> tab at the top</li>
                    <li>Choose the file <code>setup.sql</code> from your project folder</li>
                    <li>Click <strong>Go</strong> to import</li>
                    <li>Refresh this page</li>
                </ol>
                <p>After importing, you'll be able to manage tours, destinations, bookings, and all other content from this dashboard.</p>
                <a href="dashboard.php" class="btn"><i class="fas fa-sync-alt"></i> Refresh Dashboard</a>
            </div>
            <?php else: ?>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon blue"><i class="fas fa-globe"></i></div>
                    <div>
                        <div class="stat-value"><?php echo $counts['tours'] ?? 0; ?></div>
                        <div class="stat-label">Active Tours</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon orange"><i class="fas fa-calendar-check"></i></div>
                    <div>
                        <div class="stat-value"><?php echo $counts['bookings'] ?? 0; ?></div>
                        <div class="stat-label">New Bookings</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon red"><i class="fas fa-envelope"></i></div>
                    <div>
                        <div class="stat-value"><?php echo $counts['inquiries'] ?? 0; ?></div>
                        <div class="stat-label">New Inquiries</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon purple"><i class="fas fa-mail-bulk"></i></div>
                    <div>
                        <div class="stat-value"><?php echo $counts['subscribers'] ?? 0; ?></div>
                        <div class="stat-label">Subscribers</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon green"><i class="fas fa-blog"></i></div>
                    <div>
                        <div class="stat-value"><?php echo $counts['blog_posts'] ?? 0; ?></div>
                        <div class="stat-label">Blog Posts</div>
                    </div>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                <div class="card">
                    <div class="card-header">
                        <h2><i class="fas fa-calendar-check" style="color:var(--primary);"></i> Recent Bookings</h2>
                        <a href="bookings.php" class="btn btn-sm btn-outline">View All</a>
                    </div>
                    <div class="card-body" style="padding:0;">
                        <table class="data-table">
                            <thead>
                                <tr><th>Name</th><th>Tour</th><th>Status</th><th>Date</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentBookings as $b): ?>
                                <tr>
                                    <td><?php echo esc($b['name']); ?></td>
                                    <td><?php echo esc($b['tour_title'] ?? 'N/A'); ?></td>
                                    <td><span class="badge badge-<?php echo $b['status']; ?>"><?php echo ucfirst($b['status']); ?></span></td>
                                    <td><?php echo date('M j', strtotime($b['created_at'])); ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($recentBookings)): ?>
                                <tr><td colspan="4" style="text-align:center;color:var(--text-light);padding:2rem;">No bookings yet</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h2><i class="fas fa-envelope" style="color:var(--primary);"></i> Recent Inquiries</h2>
                        <a href="inquiries.php" class="btn btn-sm btn-outline">View All</a>
                    </div>
                    <div class="card-body" style="padding:0;">
                        <table class="data-table">
                            <thead>
                                <tr><th>Name</th><th>Subject</th><th>Status</th><th>Date</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentInquiries as $i): ?>
                                <tr>
                                    <td><?php echo esc($i['name']); ?></td>
                                    <td><?php echo esc($i['subject'] ?? 'General'); ?></td>
                                    <td><span class="badge badge-<?php echo $i['status']; ?>"><?php echo ucfirst($i['status']); ?></span></td>
                                    <td><?php echo date('M j', strtotime($i['created_at'])); ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($recentInquiries)): ?>
                                <tr><td colspan="4" style="text-align:center;color:var(--text-light);padding:2rem;">No inquiries yet</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
