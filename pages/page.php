<?php
require_once __DIR__ . '/../includes/functions.php';

$slug = $_GET['slug'] ?? '';
if (!$slug) { render_not_found('Page Not Found', 'No page was specified.'); }

$page = getPageBySlug($slug);
if (!$page) {
    render_not_found('Page Not Found', 'That page does not exist or has been moved. Try the menu above, or start from the homepage.');
}

$pageTitle = $page['meta_title'] ?: $page['title'] . ' | Bamba Adventures';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="/assets/images/favicon.ico" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($page['meta_description'] ?? ''); ?>">
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
        
        .page-content { max-width: 900px; margin: 0 auto; padding: 4rem 5%; }
        .page-content h2 { color: var(--primary-dark); margin: 2rem 0 1rem; font-size: 1.8rem; }
        .page-content h3 { color: var(--primary); margin: 1.5rem 0 0.75rem; font-size: 1.4rem; }
        .page-content p { color: var(--text-light); margin-bottom: 1rem; line-height: 1.8; }
        .page-content ul, .page-content ol { margin: 1rem 0 1rem 1.5rem; color: var(--text-light); line-height: 1.8; }
        .page-content li { margin-bottom: 0.5rem; }
        .page-content a { color: var(--primary-dark); font-weight: 600; }
        .page-content blockquote { border-left: 4px solid var(--accent); padding-left: 1.5rem; margin: 1.5rem 0; color: var(--text-light); font-style: italic; }
        .page-content img { max-width: 100%; border-radius: 12px; margin: 1.5rem 0; }
        
        .cta-strip { background: linear-gradient(135deg, var(--primary-dark), var(--primary)); padding: 4rem 5%; text-align: center; color: var(--white); margin-top: 3rem; }
        .cta-strip h2 { font-size: clamp(1.6rem, 3vw, 2.2rem); margin-bottom: 1rem; }
        .cta-strip a { background: var(--accent); color: var(--text-dark); padding: 0.9rem 2rem; border-radius: 30px; text-decoration: none; font-weight: 600; display: inline-block; margin-top: 1rem; }
        
        footer { background: #1a1a1a; color: var(--white); padding: 3rem 5%; text-align: center; }
        .footer-bottom { color: #666; font-size: 0.85rem; }
    </style>
</head>
<body>
    <?php include '../includes/nav.php'; ?>

    <div class="page-hero">
        <h1><?php echo htmlspecialchars($page['title']); ?></h1>
    </div>

    <div class="page-content">
        <?php echo $page['content'] ?: '<p style="text-align:center;color:var(--text-light);">Content coming soon.</p>'; ?>
    </div>

    <div class="cta-strip">
        <h2>Ready for Your Next Adventure?</h2>
        <p>Let our travel experts craft your perfect journey.</p>
        <a href="/book">Start Planning</a>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
