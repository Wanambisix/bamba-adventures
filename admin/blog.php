<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$posts = getBlogPosts();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['id'])) {
    csrf_verify();
    if ($_POST['action'] === 'toggle_status') {
        $newStatus = in_array($_POST['value'] ?? '', ['published', 'draft'], true) ? $_POST['value'] : 'draft';
        $pdo->prepare("UPDATE blog_posts SET status = ? WHERE id = ?")->execute([$newStatus, $_POST['id']]);
        echo 'OK'; exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    csrf_verify();
    $pdo->prepare("DELETE FROM blog_posts WHERE id = ?")->execute([$_POST['id']]);
    flash('Blog post deleted.');
    redirect('/admin/blog.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Blog Posts | Bamba Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main">
        <div class="topbar">
            <h1>Blog Posts (<?php echo count($posts); ?>)</h1>
            <div class="topbar-right">
                <a href="blog-edit.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add Post</a>
            </div>
        </div>
        <div class="content">
            <div class="card">
                <div class="card-body" style="padding:0;">
                    <table class="data-table">
                        <thead>
                            <tr><th>Image</th><th>Title</th><th>Category</th><th>Author</th><th>Date</th><th>Status</th><th style="width:120px;">Actions</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($posts as $p): ?>
                            <tr data-id="<?php echo $p['id']; ?>">
                                <td><img src="<?php echo esc($p['image'] ?? 'https://placehold.co/60x40?text=No+Img'); ?>" alt=""></td>
                                <td><strong><?php echo esc($p['title']); ?></strong></td>
                                <td><?php echo esc($p['category'] ?? '-'); ?></td>
                                <td><?php echo esc($p['author'] ?? '-'); ?></td>
                                <td><?php echo esc($p['published_at'] ?? $p['created_at']); ?></td>
                                <td>
                                    <div class="toggle <?php echo $p['status'] === 'published' ? 'on' : ''; ?>" onclick="toggleField(this, <?php echo $p['id']; ?>)"></div>
                                    <span class="badge badge-<?php echo $p['status'] === 'published' ? 'published' : 'draft'; ?>" style="margin-left:6px;"><?php echo ucfirst($p['status']); ?></span>
                                </td>
                                <td>
                                    <div class="actions">
                                        <a href="blog-edit.php?id=<?php echo $p['id']; ?>" class="action-btn edit"><i class="fas fa-pen"></i></a>
                                        <?php echo action_form('/admin/blog.php', ['action' => 'delete', 'id' => $p['id']], 'action-btn delete', 'fas fa-trash', 'Delete?'); ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($posts)): ?>
                            <tr><td colspan="7" class="empty-state">No blog posts. <a href="blog-edit.php">Write one</a>.</td></tr>
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
        const value = el.classList.contains('on') ? 'published' : 'draft';
        const form = new FormData();
            form.append('csrf_token', window.BAMBA_CSRF);
        form.append('action', 'toggle_status');
        form.append('id', id);
        form.append('value', value);
        fetch('blog.php', { method: 'POST', body: form })
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
