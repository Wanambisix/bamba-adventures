<?php
require_once __DIR__ . '/../includes/functions.php';

$posts = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE status = 'published' ORDER BY published_at DESC, created_at DESC");
    $stmt->execute();
    $posts = $stmt->fetchAll();
} catch (Exception $e) {
    $posts = [];
}

$pageTitle = 'Travel Blog | Bamba Adventures';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="/assets/images/favicon.ico" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary-dark: #841e22; --primary: #a02f1d; --accent: #fdb011; --text-dark: #1a1a1a; --text-light: #666; --bg-light: #f9f7f4; --white: #ffffff; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body { font-family: 'Poppins', sans-serif; color: var(--text-dark); line-height: 1.6; overflow-x: hidden; }
        h1, h2, h3, h4 { font-family: 'Poppins', sans-serif; font-weight: 700; }
        
        .page-hero { background: linear-gradient(135deg, var(--primary-dark), var(--primary)); padding: 8rem 5% 4rem; text-align: center; color: var(--white); }
        .page-hero h1 { font-size: clamp(2rem, 4vw, 3rem); margin-bottom: 0.5rem; }
        .page-hero p { opacity: 0.9; font-size: 1.1rem; }
        
        .blog-grid { max-width: 1200px; margin: 0 auto; padding: 4rem 5%; display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 2rem; }
        .blog-card { background: var(--white); border-radius: 16px; overflow: hidden; box-shadow: 0 5px 20px rgba(0,0,0,0.08); transition: all 0.3s ease; text-decoration: none; color: inherit; display: flex; flex-direction: column; }
        .blog-card:hover { transform: translateY(-6px); box-shadow: 0 15px 40px rgba(0,0,0,0.15); }
        .blog-card-img { height: 200px; background-size: cover; background-position: center; }
        .blog-card-body { padding: 1.5rem; flex: 1; display: flex; flex-direction: column; }
        .blog-card-body .date { font-size: 0.8rem; color: var(--text-light); margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 1px; }
        .blog-card-body h3 { font-size: 1.25rem; margin-bottom: 0.5rem; color: var(--text-dark); }
        .blog-card-body p { color: var(--text-light); font-size: 0.9rem; line-height: 1.6; flex: 1; }
        .blog-card-body .read-more { color: var(--primary-dark); font-weight: 600; font-size: 0.9rem; margin-top: 1rem; }
        
        .no-posts { text-align: center; padding: 4rem; color: var(--text-light); }
    </style>
</head>
<body>
    <?php include '../includes/nav.php'; ?>

    <div class="page-hero">
        <h1>Travel Blog</h1>
        <p>Stories, tips, and inspiration from around the world</p>
    </div>

    <div class="blog-grid">
        <?php if (!empty($posts)): ?>
            <?php foreach ($posts as $post): ?>
            <a href="/blog/post/<?php echo htmlspecialchars($post['slug']); ?>" class="blog-card">
                <div class="blog-card-img" style="background-image:url('<?php echo htmlspecialchars($post['image'] ?: 'https://images.unsplash.com/photo-1476514525535-07fb3b4ae5f1?w=800'); ?>')"></div>
                <div class="blog-card-body">
                    <div class="date"><?php echo date('F j, Y', strtotime($post['published_at'] ?: $post['created_at'])); ?></div>
                    <h3><?php echo htmlspecialchars($post['title']); ?></h3>
                    <p><?php echo htmlspecialchars($post['excerpt'] ?: excerpt($post['content'], 120)); ?></p>
                    <span class="read-more">Read More <i class="fas fa-arrow-right"></i></span>
                </div>
            </a>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-posts" style="grid-column:1/-1;">
                <h3>No blog posts yet</h3>
                <p>Check back soon for travel stories and tips.</p>
            </div>
        <?php endif; ?>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
