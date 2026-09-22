<?php
require_once __DIR__ . '/../includes/functions.php';
$slug = $_GET['slug'] ?? '';
if (!$slug) { header('Location: /'); exit; }

$country = getCountryBySlug($slug);
if (!$country) { header('Location: /'); exit; }

$pageTitle = $country['name'] . ' | Bamba Adventures';
$tours = getTours(['country_id' => $country['id'], 'status' => 'active']);
$isFallback = false;
// Fallback: if no tours assigned to this country yet, show tours from same destination
if (empty($tours)) {
    if (!empty($country['destination_id'])) {
        $search = '%' . $country['name'] . '%';
        $stmt = $pdo->prepare("SELECT t.*, d.name as destination_name, d.slug as destination_slug, c.name as country_name, c.slug as country_slug FROM tours t LEFT JOIN destinations d ON t.destination_id = d.id LEFT JOIN countries c ON t.country_id = c.id WHERE t.status = 'active' AND (t.destination_id = ? OR t.title LIKE ? OR t.description LIKE ?) ORDER BY t.created_at DESC");
        $stmt->execute([$country['destination_id'], $search, $search]);
    } else {
        $search = '%' . $country['name'] . '%';
        $stmt = $pdo->prepare("SELECT t.*, d.name as destination_name, d.slug as destination_slug, c.name as country_name, c.slug as country_slug FROM tours t LEFT JOIN destinations d ON t.destination_id = d.id LEFT JOIN countries c ON t.country_id = c.id WHERE t.status = 'active' AND (t.title LIKE ? OR t.description LIKE ?) ORDER BY t.created_at DESC");
        $stmt->execute([$search, $search]);
    }
    $tours = $stmt->fetchAll();
    $isFallback = !empty($tours);
}
$allDestinations = getDestinations();
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

        .country-hero { min-height: 340px; position: relative; display: flex; align-items: flex-end; background: linear-gradient(135deg, var(--primary-dark), var(--primary)); }
        .country-hero-content { position: relative; z-index: 2; padding: 4rem 5% 3.5rem; max-width: 1400px; margin: 0 auto; width: 100%; }
        .country-tag { display: inline-block; background: var(--accent); color: var(--text-dark); font-size: 0.78rem; font-weight: 700; letter-spacing: 3px; text-transform: uppercase; padding: 0.35rem 1rem; border-radius: 20px; margin-bottom: 1rem; }
        .country-hero-content h1 { font-size: clamp(2.5rem, 6vw, 5rem); color: var(--white); line-height: 1.05; text-shadow: 2px 2px 12px rgba(0,0,0,0.4); margin-bottom: 0.5rem; }
        .country-hero-content p { font-size: 1.15rem; color: rgba(255,255,255,0.9); max-width: 600px; }
        .breadcrumb { color: rgba(255,255,255,0.8); font-size: 0.9rem; margin-bottom: 1rem; }
        .breadcrumb a { color: var(--accent); text-decoration: none; }
        .breadcrumb a:hover { text-decoration: underline; }

        .intro-section { padding: 5rem 5%; max-width: 1400px; margin: 0 auto; display: grid; grid-template-columns: 1fr 1fr; gap: 5rem; align-items: start; }
        .intro-text .section-tag { color: var(--primary); font-weight: 600; text-transform: uppercase; letter-spacing: 3px; font-size: 0.85rem; display: block; margin-bottom: 1rem; }
        .intro-text h2 { font-size: clamp(1.8rem, 3.5vw, 2.6rem); margin-bottom: 1.5rem; color: var(--text-dark); }
        .intro-text p { color: var(--text-light); line-height: 1.9; font-size: 1rem; }

        .tours-section { padding: 0 5% 6rem; max-width: 1400px; margin: 0 auto; }
        .tours-section .section-header { text-align: center; margin-bottom: 3.5rem; }
        .tours-section .section-title { font-size: clamp(1.8rem, 3.5vw, 2.6rem); color: var(--text-dark); margin-bottom: 0.75rem; }
        .tours-section .section-subtitle { color: var(--text-light); font-size: 1rem; max-width: 560px; margin: 0 auto; }
        .packages-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 2rem; }
        .package-card { background: var(--white); border-radius: 20px; overflow: hidden; box-shadow: 0 5px 20px rgba(0,0,0,0.08); transition: all 0.35s ease; display: flex; flex-direction: column; }
        .package-card:hover { transform: translateY(-8px); box-shadow: 0 20px 50px rgba(0,0,0,0.15); }
        .package-image { position: relative; height: 230px; overflow: hidden; }
        .package-image img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.6s ease; }
        .package-card:hover .package-image img { transform: scale(1.08); }
        .country-label { position: absolute; bottom: 12px; left: 12px; background: rgba(26,26,26,0.72); backdrop-filter: blur(4px); color: #fff; font-size: 0.72rem; font-weight: 600; letter-spacing: 0.5px; padding: 0.28rem 0.7rem; border-radius: 20px; display: flex; align-items: center; gap: 0.3rem; pointer-events: none; }
        .package-content { padding: 1.75rem; display: flex; flex-direction: column; flex: 1; }
        .package-meta { display: flex; gap: 1rem; margin-bottom: 0.75rem; font-size: 0.85rem; color: var(--text-light); }
        .package-meta i { color: var(--primary); }
        .package-title { font-size: 1.35rem; margin-bottom: 0.75rem; color: var(--text-dark); }
        .package-footer { display: flex; justify-content: space-between; align-items: center; margin-top: 1.25rem; padding-top: 1.25rem; border-top: 1px solid #eee; }
        .price { font-size: 1.4rem; font-weight: 700; color: var(--primary-dark); }
        .price span { font-size: 0.85rem; color: var(--text-light); font-weight: 400; }
        .btn-small { background: var(--primary-dark); color: var(--white); padding: 0.55rem 1.4rem; border-radius: 25px; text-decoration: none; font-size: 0.85rem; font-weight: 500; transition: all 0.3s ease; }
        .btn-small:hover { background: var(--primary); }
        .card-desc { display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }

        .cta-strip { background: linear-gradient(135deg, var(--primary-dark), var(--primary)); padding: 4.5rem 5%; text-align: center; color: var(--white); }
        .cta-strip h2 { font-size: clamp(1.8rem, 3vw, 2.5rem); margin-bottom: 1rem; }
        .cta-strip p { font-size: 1.1rem; margin-bottom: 2rem; opacity: 0.9; max-width: 580px; margin-left: auto; margin-right: auto; }
        .btn-gold { background: var(--accent); color: var(--text-dark); padding: 0.9rem 2rem; border-radius: 30px; text-decoration: none; font-weight: 600; transition: all 0.3s ease; display: inline-block; }
        .btn-gold:hover { transform: translateY(-3px); ; }

        .fade-in { opacity: 0; transform: translateY(28px); transition: all 0.8s ease; }
        .fade-in.visible { opacity: 1; transform: translateY(0); }

        @media(max-width:1100px){ .packages-grid { grid-template-columns: repeat(2,1fr); } .intro-section { grid-template-columns: 1fr; gap: 2.5rem; } }
        @media(max-width:640px){ .packages-grid{grid-template-columns:1fr;} }
    </style>
</head>
<body id="top">

<?php include '../includes/nav.php'; ?>

    <div class="country-hero">
        <div class="country-hero-content">
            <div class="breadcrumb">
                <a href="/destination/<?= htmlspecialchars($country['destination_slug'] ?? '') ?>"><?= htmlspecialchars($country['destination_name'] ?? 'Destinations') ?></a> &rsaquo; <?= htmlspecialchars($country['name']) ?>
            </div>
            <span class="country-tag">Explore</span>
            <h1><?= htmlspecialchars($country['name']) ?></h1>
            <p><?= htmlspecialchars($country['description'] ?: 'Discover amazing tours and adventures') ?></p>
        </div>
    </div>

    <div class="intro-section">
        <div class="intro-text fade-in">
            <span class="section-tag">Discover <?= htmlspecialchars($country['name']) ?></span>
            <h2>Why Visit <?= htmlspecialchars($country['name']) ?>?</h2>
            <p><?= nl2br(htmlspecialchars($country['description'] ?: 'Experience the beauty and adventure of ' . $country['name'] . ' with Bamba Adventures.')) ?></p>
        </div>
        <div class="intro-text fade-in" style="background:var(--bg-light);border-radius:20px;padding:2.5rem;">
            <h3 style="font-size:1.3rem;margin-bottom:1rem;color:var(--primary-dark);">Travel Highlights</h3>
            <p style="color:var(--text-light);">Our expert guides craft unforgettable experiences across <?= htmlspecialchars($country['name']) ?>. From wildlife safaris to cultural immersions, every journey is tailored to create lasting memories.</p>
        </div>
    </div>

    <div class="tours-section">
        <div class="section-header fade-in">
            <h2 class="section-title">Tour Packages in <?= htmlspecialchars($country['name']) ?></h2>
            <p class="section-subtitle">Handpicked itineraries designed for every type of traveller<?= $isFallback ? ' <span style="display:block;margin-top:0.5rem;font-size:0.85rem;color:var(--text-light);">(Showing related tours from the region — <a href="/book" style="color:var(--primary-dark);font-weight:600;">contact us</a> for ' . htmlspecialchars($country['name']) . '-specific packages)</span>' : '' ?></p>
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
                        <?php $labelName = $t['country_name'] ?? $country['name'] ?? ''; ?>
                        <?php if ($labelName): ?>
                        <span class="country-label"><i class="fas fa-map-marker-alt" style="font-size:0.65rem;color:var(--accent)"></i> <?= htmlspecialchars($labelName) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="package-content">
                        <div class="package-meta">
                            <span><i class="far fa-clock"></i> <?= htmlspecialchars($t['duration'] ?: 'Varies') ?></span>
                            <span><i class="far fa-user"></i> Max <?= htmlspecialchars($t['max_group_size'] ?: '12') ?></span>
                        </div>
                        <h3 class="package-title"><?= htmlspecialchars($t['title']) ?></h3>
                        <p class="card-desc"><?= htmlspecialchars(strip_tags($t['description'] ?: 'An unforgettable adventure awaits.')) ?></p>
                        <div class="package-footer">
                            <div class="price"><?= formatPrice($t['price'], $t['price_note'] ?? '') ?></div>
                            <a href="/tour/<?= htmlspecialchars($t['slug']) ?>" class="btn-small">Details</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="grid-column:1/-1;text-align:center;padding:3rem;color:var(--text-light);">
                    <p>No tours available for <?= htmlspecialchars($country['name']) ?> yet. <a href="/book" style="color:var(--primary-dark);font-weight:600;">Contact us</a> to plan a custom trip.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="cta-strip">
        <h2>Ready to Explore <?= htmlspecialchars($country['name']) ?>?</h2>
        <p>Let our experts craft your perfect itinerary. Custom trips available for groups, families, and solo travelers.</p>
        <a href="/book" class="btn-gold">Get Free Quote</a>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script>
    const observer = new IntersectionObserver((entries) => entries.forEach(e => { if(e.isIntersecting) e.target.classList.add('visible'); }), { threshold: 0.1 });
    document.querySelectorAll('.fade-in').forEach(el => observer.observe(el));
    </script>
</body>
</html>
