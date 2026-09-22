<?php
require_once __DIR__ . '/../../includes/functions.php';
$slug = $_GET['slug'] ?? '';
if (!$slug) { header('Location: /'); exit; }

$category = getTourCategoryBySlug($slug);
if (!$category) { header('Location: /'); exit; }

$pageTitle = $category['name'] . ' Tours | Bamba Adventures';
$tours = getToursByCategorySlug($slug);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="/assets/images/favicon.ico" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary-dark: #841e22; --primary: #a02f1d; --accent: #fdb011; --text-dark: #1a1a1a; --text-light: #666; --bg-light: #f9f7f4; --white: #ffffff; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body { font-family: 'Poppins', sans-serif; color: var(--text-dark); line-height: 1.6; overflow-x: hidden; }
        h1,h2,h3,h4 { font-family: 'Poppins', sans-serif; font-weight: 700; }

        .cat-hero { background: linear-gradient(135deg, var(--primary-dark), var(--primary)); padding: 8rem 5% 4rem; text-align: center; color: var(--white); }
        .cat-hero h1 { font-size: clamp(2rem, 4vw, 3rem); margin-bottom: 0.5rem; }
        .cat-hero p { opacity: 0.9; font-size: 1.1rem; max-width: 600px; margin: 0 auto; }

        .tours-section { padding: 4rem 5%; max-width: 1400px; margin: 0 auto; }
        .section-header { text-align: center; margin-bottom: 3.5rem; }
        .section-title { font-size: clamp(1.8rem, 3.5vw, 2.6rem); color: var(--text-dark); margin-bottom: 0.75rem; }
        .section-subtitle { color: var(--text-light); font-size: 1rem; max-width: 560px; margin: 0 auto; }
        .packages-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 2rem; }
        .package-card { background: var(--white); border-radius: 20px; overflow: hidden; box-shadow: 0 5px 20px rgba(0,0,0,0.08); transition: all 0.35s ease; display: flex; flex-direction: column; }
        .package-card:hover { transform: translateY(-8px); box-shadow: 0 20px 50px rgba(0,0,0,0.15); }
        .package-image { position: relative; height: 230px; overflow: hidden; }
        .package-image img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.6s ease; }
        .package-card:hover .package-image img { transform: scale(1.08); }
        .package-content { padding: 1.75rem; display: flex; flex-direction: column; flex: 1; }
        .package-meta { display: flex; gap: 1rem; margin-bottom: 0.75rem; font-size: 0.85rem; color: var(--text-light); }
        .package-meta i { color: var(--primary); }
        .package-title { font-size: 1.35rem; margin-bottom: 0.75rem; color: var(--text-dark); }
        .package-footer { display: flex; justify-content: space-between; align-items: center; margin-top: 1.25rem; padding-top: 1.25rem; border-top: 1px solid #eee; }
        .price { font-size: 1.4rem; font-weight: 700; color: var(--primary-dark); }
        .btn-small { background: var(--primary-dark); color: var(--white); padding: 0.55rem 1.4rem; border-radius: 25px; text-decoration: none; font-size: 0.85rem; font-weight: 500; transition: all 0.3s ease; }
        .btn-small:hover { background: var(--primary); }

        .cta-strip { background: linear-gradient(135deg, var(--primary-dark), var(--primary)); padding: 4.5rem 5%; text-align: center; color: var(--white); }
        .cta-strip h2 { font-size: clamp(1.8rem, 3vw, 2.5rem); margin-bottom: 1rem; }
        .cta-strip p { font-size: 1.1rem; margin-bottom: 2rem; opacity: 0.9; max-width: 580px; margin-left: auto; margin-right: auto; }
        .btn-gold { background: var(--accent); color: var(--text-dark); padding: 0.9rem 2rem; border-radius: 30px; text-decoration: none; font-weight: 600; transition: all 0.3s ease; display: inline-block; }
        .btn-gold:hover { transform: translateY(-3px); ; }

        .fade-in { opacity: 0; transform: translateY(28px); transition: all 0.8s ease; }
        .fade-in.visible { opacity: 1; transform: translateY(0); }

        @media(max-width:1100px){ .packages-grid { grid-template-columns: repeat(2,1fr); } }
        @media(max-width:640px){ .packages-grid{grid-template-columns:1fr;} }
    </style>
</head>
<body id="top">

<?php include '../../includes/nav.php'; ?>

    <div class="cat-hero">
        <h1><?= htmlspecialchars($category['name']) ?> Tours</h1>
        <p><?= htmlspecialchars($category['description'] ?: 'Explore our curated ' . $category['name'] . ' tour packages') ?></p>
    </div>

    <div class="tours-section">
        <div class="section-header fade-in">
            <h2 class="section-title">Available Packages</h2>
            <p class="section-subtitle">Handpicked itineraries designed for every type of traveller</p>
        </div>
        <div class="packages-grid">
            <?php if (!empty($tours)): ?>
                <?php foreach ($tours as $t):
                    $imgs = json_decode($t['images'] ?? '[]', true);
                    $img = $imgs[0] ?? $t['image'] ?? 'https://images.unsplash.com/photo-1476514525535-07fb3b4ae5f1?w=800&q=80';
                ?>
                <div class="package-card fade-in">
                    <div class="package-image">
                        <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($t['title']) ?>">
                    </div>
                    <div class="package-content">
                        <div class="package-meta">
                            <span><i class="far fa-clock"></i> <?= htmlspecialchars($t['duration'] ?: 'Varies') ?></span>
                            <span><i class="far fa-user"></i> Max <?= htmlspecialchars($t['max_group_size'] ?: '12') ?></span>
                        </div>
                        <h3 class="package-title"><?= htmlspecialchars($t['title']) ?></h3>
                        <p><?= htmlspecialchars(strip_tags($t['description'] ?: 'An unforgettable adventure awaits.')) ?></p>
                        <div class="package-footer">
                            <div class="price"><?= formatPrice($t['price'], $t['price_note'] ?? '') ?></div>
                            <a href="/tour/<?= htmlspecialchars($t['slug']) ?>" class="btn-small">Details</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="grid-column:1/-1;text-align:center;padding:3rem;color:var(--text-light);">
                    <p>No <?= htmlspecialchars($category['name']) ?> tours available yet. <a href="/book" style="color:var(--primary-dark);font-weight:600;">Contact us</a> to plan a custom trip.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="cta-strip">
        <h2>Can't Find What You're Looking For?</h2>
        <p>Let our experts craft your perfect itinerary. Custom trips available for groups, families, and solo travelers.</p>
        <a href="/book" class="btn-gold">Get Free Quote</a>
    </div>

    <?php include '../../includes/footer.php'; ?>

    <script>
    const observer = new IntersectionObserver((entries) => entries.forEach(e => { if(e.isIntersecting) e.target.classList.add('visible'); }), { threshold: 0.1 });
    document.querySelectorAll('.fade-in').forEach(el => observer.observe(el));
    </script>
</body>
</html>
