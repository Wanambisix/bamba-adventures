<?php
require_once __DIR__ . '/../includes/functions.php';
$slug = $_GET['slug'] ?? '';
if (!$slug) { render_not_found('Destination Not Found', 'No destination was specified.'); }

$destination = getDestinationBySlug($slug);
if (!$destination) {
    render_not_found('Destination Not Found', 'We do not have a page for that destination yet. Browse everywhere we travel instead.');
}

$pageTitle = $destination['meta_title'] ?: $destination['name'] . ' | Bamba Adventures';
$countries = getCountries($destination['id'], 'active');
$tours = getTours(['destination_id' => $destination['id'], 'status' => 'active']);
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


        .continent-hero { min-height: 360px; position: relative; display: flex; align-items: flex-end; background: linear-gradient(135deg, var(--primary-dark), var(--primary)); }
        .continent-hero-content { position: relative; z-index: 2; padding: 4rem 5% 3.5rem; max-width: 1400px; margin: 0 auto; width: 100%; }
        .continent-tag { display: inline-block; background: var(--accent); color: var(--text-dark); font-size: 0.78rem; font-weight: 700; letter-spacing: 3px; text-transform: uppercase; padding: 0.35rem 1rem; border-radius: 20px; margin-bottom: 1rem; }
        .continent-hero-content h1 { font-size: clamp(2.5rem, 6vw, 5rem); color: var(--white); line-height: 1.05; text-shadow: 2px 2px 12px rgba(0,0,0,0.4); margin-bottom: 0.5rem; }
        .continent-hero-content p { font-size: 1.15rem; color: rgba(255,255,255,0.9); max-width: 600px; }

        .intro-section { padding: 5rem 5%; max-width: 1400px; margin: 0 auto; display: grid; grid-template-columns: 1fr 1fr; gap: 5rem; align-items: start; }
        .intro-text .section-tag { color: var(--primary); font-weight: 600; text-transform: uppercase; letter-spacing: 3px; font-size: 0.85rem; display: block; margin-bottom: 1rem; }
        .intro-text h2 { font-size: clamp(1.8rem, 3.5vw, 2.6rem); margin-bottom: 1.5rem; color: var(--text-dark); }
        .intro-text p { color: var(--text-light); line-height: 1.9; font-size: 1rem; }

        .countries-section { padding: 0 5% 6rem; max-width: 1400px; margin: 0 auto; }
        .countries-section .section-header { text-align: center; margin-bottom: 3.5rem; }
        .countries-section .section-title { font-size: clamp(1.8rem, 3.5vw, 2.6rem); color: var(--text-dark); margin-bottom: 0.75rem; }
        .countries-section .section-subtitle { color: var(--text-light); font-size: 1rem; max-width: 560px; margin: 0 auto; }
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

        footer { background: #1a1a1a; color: var(--white); padding: 4rem 5% 2rem; }
        .footer-grid { max-width: 1400px; margin: 0 auto; display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 3rem; margin-bottom: 3rem; }
        .footer-col h4 { color: var(--accent); margin-bottom: 1.2rem; font-size: 1.1rem; }
        .footer-col p { color: #aaa; line-height: 1.8; margin-bottom: 1rem; font-size: 0.92rem; }
        .footer-links { list-style: none; }
        .footer-links li { margin-bottom: 0.65rem; }
        .footer-links a { color: #aaa; text-decoration: none; transition: color 0.3s ease; display: flex; align-items: flex-start; gap: 0.5rem; font-size: 0.9rem; line-height: 1.5; }
        .footer-links a:hover { color: var(--accent); }
        .footer-bottom { border-top: 1px solid #333; padding-top: 1.5rem; text-align: center; color: #666; font-size: 0.85rem; max-width: 1400px; margin: 0 auto; }

        .floating-wrap { position: fixed; bottom: 2rem; right: 2rem; display: flex; flex-direction: column; gap: 0.75rem; z-index: 999; align-items: flex-end; }
        .whatsapp-btn { width: 58px; height: 58px; background: #25D366; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 1.7rem; text-decoration: none; transition: all 0.3s ease; }
        .whatsapp-btn:hover { transform: scale(1.1); }
        .back-to-top { width: 50px; height: 50px; background: var(--primary-dark); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 1.1rem; text-decoration: none; transition: all 0.3s ease; opacity: 0; pointer-events: none; }
        .back-to-top.visible { opacity: 1; pointer-events: all; }
        .back-to-top:hover { background: var(--primary); }
        .fade-in { opacity: 0; transform: translateY(28px); transition: all 0.8s ease; }
        .fade-in.visible { opacity: 1; transform: translateY(0); }

        @media(max-width:1100px){ .packages-grid { grid-template-columns: repeat(2,1fr); } .intro-section { grid-template-columns: 1fr; gap: 2.5rem; } }
        @media(max-width:968px){ .footer-grid{grid-template-columns:1fr 1fr; gap:2rem;} }
        @media(max-width:640px){ .packages-grid{grid-template-columns:1fr;} .footer-grid{grid-template-columns:1fr;} }
    </style>
</head>
<body id="top">

<?php include '../includes/nav.php'; ?>


    <div class="continent-hero">
        <div class="continent-hero-content">
            <span class="continent-tag">Destination</span>
            <h1><?= htmlspecialchars($destination['name']) ?></h1>
            <p><?= htmlspecialchars($destination['description'] ?: 'Explore amazing tours and adventures') ?></p>
        </div>
    </div>

    <div class="intro-section">
        <div class="intro-text fade-in">
            <span class="section-tag">Discover <?= htmlspecialchars($destination['name']) ?></span>
            <h2>Why Visit <?= htmlspecialchars($destination['name']) ?>?</h2>
            <p><?= nl2br(htmlspecialchars($destination['long_content'] ?: $destination['description'] ?: 'Experience the beauty and adventure of ' . $destination['name'] . ' with Bamba Adventures.')) ?></p>
        </div>
        <div class="intro-text fade-in" style="background:var(--bg-light);border-radius:20px;padding:2.5rem;">
            <h3 style="font-size:1.3rem;margin-bottom:1rem;color:var(--primary-dark);">Travel Highlights</h3>
            <p style="color:var(--text-light);">Our expert guides craft unforgettable experiences across <?= htmlspecialchars($destination['name']) ?>. From wildlife safaris to cultural immersions, every journey is tailored to create lasting memories.</p>
        </div>
    </div>

    <div class="countries-section">
        <div class="section-header fade-in">
            <h2 class="section-title">Countries in <?= htmlspecialchars($destination['name']) ?></h2>
            <p class="section-subtitle">Select a country to explore its tour packages</p>
        </div>
        <div class="packages-grid">
            <?php if (!empty($countries)): ?>
                <?php foreach ($countries as $c): ?>
                <div class="package-card fade-in">
                    <div class="package-image">
                        <img src="<?= htmlspecialchars($c['image'] ?? 'https://images.unsplash.com/photo-1476514525535-07fb3b4ae5f1?w=800&q=80') ?>" alt="<?= htmlspecialchars($c['name']) ?>">
                    </div>
                    <div class="package-content">
                        <h3 class="package-title"><?= htmlspecialchars($c['name']) ?></h3>
                        <p><?= htmlspecialchars($c['description'] ?: 'Explore amazing tours in ' . $c['name'] . '.') ?></p>
                        <div class="package-footer">
                            <span style="font-size:0.85rem;color:var(--text-light);"><i class="fas fa-map-marker-alt" style="color:var(--primary);"></i> <?= htmlspecialchars($destination['name']) ?></span>
                            <a href="/country/<?= htmlspecialchars($c['slug']) ?>" class="btn-small">Explore</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php elseif (!empty($tours)): ?>
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
                    <p>No countries or tours available in this region yet. <a href="/book" style="color:var(--primary-dark);font-weight:600;">Contact us</a> to plan a custom trip.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="cta-strip">
        <h2>Ready to Explore <?= htmlspecialchars($destination['name']) ?>?</h2>
        <p>Let our experts craft your perfect itinerary. Custom trips available for groups, families, and solo travelers.</p>
        <a href="/book" class="btn-gold">Get Free Quote</a>
    </div>

    <footer>
        <div class="footer-grid">
            <div class="footer-col">
                <img src="/assets/images/bamba-logo.png" alt="Bamba Adventures" style="height:50px;margin-bottom:1rem;filter:brightness(0) invert(1);">
                <p>Your trusted partner for extraordinary journeys across Africa, Asia, and the Middle East.</p>
            </div>
            <div class="footer-col">
                <h4>Destinations</h4>
                <ul class="footer-links">
                    <?php foreach (array_slice($allDestinations, 0, 5) as $d): ?>
                    <li><a href="/destination/<?= htmlspecialchars($d['slug']) ?>"><?= htmlspecialchars($d['name']) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Company</h4>
                <ul class="footer-links">
                    <li><a href="/about">About Us</a></li>
                    <li><a href="/blog">Blog</a></li>
                    <li><a href="/page/careers">Careers</a></li>
                    <li><a href="/page/terms">Terms</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Contact</h4>
                <ul class="footer-links">
                    <li><a href="tel:+254706606606"><i class="fas fa-phone"></i> +254 706 606 606</a></li>
                    <li><a href="mailto:info@bambaadventures.co.ke"><i class="fas fa-envelope"></i> info@bambaadventures.co.ke</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?= date('Y') ?> Bamba Adventures. All rights reserved.</p>
        </div>
    </footer>

    <div class="floating-wrap">
        <a href="#top" class="back-to-top" id="backToTop"><i class="fas fa-arrow-up"></i></a>
        <a href="https://wa.me/254706606606" class="whatsapp-btn" target="_blank"><i class="fab fa-whatsapp"></i></a>
    </div>

    <script>
    const backToTop = document.getElementById('backToTop');
    window.addEventListener('scroll', () => backToTop.classList.toggle('visible', window.scrollY > 400));
    const observer = new IntersectionObserver((entries) => entries.forEach(e => { if(e.isIntersecting) e.target.classList.add('visible'); }), { threshold: 0.1 });
    document.querySelectorAll('.fade-in').forEach(el => observer.observe(el));
    </script>
</body>
</html>
