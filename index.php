<?php
// ================================================================
// BAMBA ADVENTURES — Homepage (Original Design + Dynamic Content)
// ================================================================

$siteName = 'Bamba Adventures';
$siteTagline = 'Premium Tours & Safaris';
$heroTitle = 'Premium Tours in Kenya and Beyond';
$heroSubtitle = 'Curated luxury tours, safaris & adventures across 7 continents';

// Try to load database and dynamic content
try {
    require_once __DIR__ . '/includes/functions.php';
    
    $siteName = getSetting('site_name', $siteName);
    $siteTagline = getSetting('site_tagline', $siteTagline);
    $heroTitle = getSetting('hero_title', $heroTitle);
    $heroSubtitle = getSetting('hero_subtitle', $heroSubtitle);
    
    $dbAvailable = true;
    $destinations = getDestinations();
    $featuredDestinations = getDestinations('active', 'yes');
    $services = getServices();
    $featuredServices = getServices('active', 'yes');
    $allTours = getTours(['status' => 'active']);
    $featuredTours = getTours(['status' => 'active', 'featured' => 'yes']);
    $testimonials = getTestimonials(6);
    
    // Categorize tours: Kenyan (category = 'kenyan') vs International (everything else)
    $kenyanTours = getTours(['status' => 'active', 'category_slug' => 'kenyan']);
    $kenyanTourIds = array_column($kenyanTours, 'id');
    $intlTours = [];

    foreach ($allTours as $tour) {
        if (!in_array($tour['id'], $kenyanTourIds)) {
            $intlTours[] = $tour;
        }
    }

    // If no categorized tours, use featured tours as fallback
    if (empty($kenyanTours) && empty($intlTours) && !empty($featuredTours)) {
        $kenyanTours = array_slice($featuredTours, 0, 3);
        $intlTours = array_slice($featuredTours, 3, 4);
    }

    // Build live search data for the search bar
    $searchIndex = [];
    foreach ($allTours as $t) {
        $imgs = json_decode($t['images'] ?? '[]', true);
        $img  = $imgs[0] ?? $t['image'] ?? '';
        $keywords = array_values(array_unique(array_filter(array_merge(
            preg_split('/\s+/', strtolower($t['title'])),
            $t['destination_name'] ? [strtolower($t['destination_name'])] : [],
            $t['country_name']     ? [strtolower($t['country_name'])]     : [],
            $t['description']      ? array_slice(preg_split('/\s+/', strtolower($t['description'])), 0, 12) : []
        ))));
        $searchIndex[] = [
            'title'      => $t['title'],
            'type'       => $t['destination_name'] ? $t['destination_name'] . ' Tour' : 'Tour Package',
            'badge'      => $t['country_name'] ?: ($t['destination_name'] ?: 'Tour'),
            'img'        => $img,
            'url'        => '/tour/' . $t['slug'],
            'keywords'   => $keywords,
            'price'      => $t['price'] ? (float)$t['price'] : null,
            'price_note' => $t['price_note'] ?? '',
        ];
    }
    foreach ($destinations as $d) {
        $searchIndex[] = [
            'title'    => $d['name'],
            'type'     => 'Destination',
            'badge'    => 'Explore',
            'img'      => $d['image'] ?? '',
            'url'      => '/destination/' . $d['slug'],
            'keywords' => array_values(array_unique(array_filter(array_merge(
                preg_split('/\s+/', strtolower($d['name'])),
                $d['description'] ? array_slice(preg_split('/\s+/', strtolower($d['description'])), 0, 6) : []
            )))),
        ];
    }
    foreach ($services as $s) {
        $searchIndex[] = [
            'title'    => $s['name'],
            'type'     => 'Service',
            'badge'    => 'Service',
            'img'      => $s['image'] ?? '',
            'url'      => '/service/' . $s['slug'],
            'keywords' => array_values(array_unique(array_filter(array_merge(
                preg_split('/\s+/', strtolower($s['name'])),
                $s['description'] ? array_slice(preg_split('/\s+/', strtolower($s['description'])), 0, 6) : []
            )))),
        ];
    }

} catch (Exception $e) {
    $dbAvailable = false;
    $destinations = [];
    $featuredDestinations = [];
    $services = [];
    $featuredServices = [];
    $kenyanTours = [];
    $intlTours = [];
    $testimonials = [];
    $searchIndex = [];
}

// Helper functions
function tourImage($tour) {
    if (!empty($tour['images'])) {
        $images = json_decode($tour['images'], true);
        if (!empty($images[0])) return $images[0];
    }
    if (!empty($tour['image'])) return $tour['image'];
    return 'https://images.unsplash.com/photo-1476514525535-07fb3b4ae5f1?auto=format&fit=crop&w=800&q=80';
}

function tourBadge($tour) {
    $badge = $tour['badge'] ?? '';
    if ($badge) return $badge;
    $destName = $tour['destination_name'] ?? '';
    if (stripos($destName, 'kenya') !== false) return 'Safari';
    if (stripos($destName, 'beach') !== false || stripos($tour['title'], 'beach') !== false) return 'Beach';
    if (stripos($tour['title'], 'luxury') !== false) return 'Luxury';
    if (stripos($tour['title'], 'trek') !== false || stripos($tour['title'], 'climb') !== false) return 'Adventure';
    return 'Popular';
}

function tourDuration($tour) {
    return $tour['duration'] ?: 'Varies';
}

function tourGroupSize($tour) {
    if (!empty($tour['max_group_size'])) {
        return 'Up to ' . $tour['max_group_size'];
    }
    return '2-6 People';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="/assets/images/favicon.ico" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($siteName) ?> | <?= htmlspecialchars($siteTagline) ?></title>
<?php
$_hProto   = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$_hHost    = $_SERVER['HTTP_HOST'] ?? 'bambaadventures.co.ke';
$_hSiteUrl = rtrim(getSetting('site_url', $_hProto . '://' . $_hHost), '/');
$_hDesc    = getSetting('meta_description_default', 'Your trusted partner for extraordinary journeys across Africa, Asia and the Middle East. Curated safaris, tours & adventures.');
$_hOgImg   = $_hSiteUrl . getSetting('og_image', '/assets/images/bamba-og.jpg');
$_hTitle   = $siteName . ' | ' . $siteTagline;
?>
    <meta name="description" content="<?= htmlspecialchars($_hDesc) ?>">
    <link rel="canonical" href="<?= htmlspecialchars($_hSiteUrl . '/') ?>">
    <meta name="robots" content="index, follow">

    <!-- Open Graph -->
    <meta property="og:type"         content="website">
    <meta property="og:site_name"    content="<?= htmlspecialchars($siteName) ?>">
    <meta property="og:title"        content="<?= htmlspecialchars($_hTitle) ?>">
    <meta property="og:description"  content="<?= htmlspecialchars($_hDesc) ?>">
    <meta property="og:url"          content="<?= htmlspecialchars($_hSiteUrl . '/') ?>">
    <meta property="og:image"        content="<?= htmlspecialchars($_hOgImg) ?>">
    <meta property="og:image:width"  content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:locale"       content="en_US">

    <!-- Twitter Card -->
    <meta name="twitter:card"        content="summary_large_image">
    <meta name="twitter:site"        content="@adventuresbamba">
    <meta name="twitter:title"       content="<?= htmlspecialchars($_hTitle) ?>">
    <meta name="twitter:description" content="<?= htmlspecialchars($_hDesc) ?>">
    <meta name="twitter:image"       content="<?= htmlspecialchars($_hOgImg) ?>">

    <!-- JSON-LD: Organization -->
    <script type="application/ld+json"><?php echo json_encode([
        '@context'  => 'https://schema.org',
        '@type'     => 'TravelAgency',
        'name'      => $siteName,
        'url'       => $_hSiteUrl,
        'logo'      => ['@type' => 'ImageObject', 'url' => $_hSiteUrl . '/assets/images/bamba-logo.png'],
        'image'     => $_hOgImg,
        'telephone' => getSetting('contact_phone', '+254706606606'),
        'email'     => getSetting('contact_email', 'info@bambaadventures.co.ke'),
        'address'   => ['@type' => 'PostalAddress', 'streetAddress' => 'Ratanssi Educational Trust Building, 2nd Floor', 'addressLocality' => 'Nairobi', 'addressCountry' => 'KE'],
        'sameAs'    => ['https://www.facebook.com/bambaadventuresnevents/', 'https://x.com/adventuresbamba', 'https://www.instagram.com/bamba_adventures/', 'https://www.youtube.com/channel/UCzI138kTQQiKC7rZSBDYi7A'],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?></script>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-dark: #841e22;
            --primary: #a02f1d;
            --accent: #fdb011;
            --text-dark: #1a1a1a;
            --text-light: #666;
            --bg-light: #f9f7f4;
            --white: #ffffff;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        html { scroll-behavior: smooth; }

        body {
            font-family: 'Poppins', sans-serif;
            color: var(--text-dark);
            line-height: 1.6;
            overflow-x: hidden;
        }

        h1, h2, h3, h4 { font-family: 'Poppins', sans-serif; font-weight: 700; }
/* ─── SEARCH SECTION (below hero) ─── */
        .search-section {
            background: var(--primary-dark);
            padding: 2.2rem 5%;
            position: relative;
            z-index: 10;
        }
        .search-section-inner {
            max-width: 700px;
            margin: 0 auto;
        }
        .search-section-inner p {
            text-align: center;
            font-size: 0.88rem;
            color: var(--white);
            margin-bottom: 0.85rem;
            letter-spacing: 0.5px;
        }

        /* ─── HERO SEARCH ─── */
        .hero-search-wrap {
            position: relative;
            width: 100%;
        }
        .hero-search-wrap input {
            width: 100%;
            padding: 1.1rem 1.5rem 1.1rem 3.4rem;
            border-radius: 50px;
            border: 2px solid var(--accent);
            background: var(--white);
            color: var(--text-dark);
            font-size: 1rem;
            font-family: 'Poppins', sans-serif;
            outline: none;
            box-shadow: 0 2px 16px rgba(0,0,0,0.15);
            transition: all 0.3s ease;
        }
        .hero-search-wrap input::placeholder { color: #aaa; }
        .hero-search-wrap input:focus {
            border-color: var(--accent);
            background: #fff;
            
        }
        .hero-search-wrap .search-icon {
            position: absolute;
            left: 1.2rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--primary-dark);
            font-size: 1rem;
            pointer-events: none;
        }
        #search-results-dropdown {
            display: none;
            position: absolute;
            top: calc(100% + 10px);
            left: 0;
            right: 0;
            width: 100%;
            background: var(--white);
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            z-index: 2000;
            overflow: hidden;
            max-height: 440px;
            overflow-y: auto;
        }
        #search-results-dropdown.active { display: block; }
        .search-result-item {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            padding: 0.75rem 1.2rem;
            cursor: pointer;
            transition: background 0.2s ease;
            border-bottom: 1px solid #f0f0f0;
            text-decoration: none;
        }
        .search-result-item:last-child { border-bottom: none; }
        .search-result-item:hover { background: var(--bg-light); }
        .search-result-img {
            width: 64px;
            height: 56px;
            border-radius: 10px;
            object-fit: cover;
            flex-shrink: 0;
            background: var(--bg-light);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .search-result-img img {
            width: 64px;
            height: 56px;
            border-radius: 10px;
            object-fit: cover;
            display: block;
        }
        .search-result-info { flex: 1; min-width: 0; }
        .search-result-info .result-title { font-weight: 600; font-size: 0.9rem; color: var(--text-dark); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .search-result-info .result-sub { font-size: 0.78rem; color: var(--text-light); }
        .search-result-info .result-badge {
            display: inline-block;
            background: var(--accent);
            color: var(--text-dark);
            font-size: 0.68rem;
            font-weight: 600;
            padding: 0.1rem 0.5rem;
            border-radius: 10px;
            margin-top: 0.2rem;
        }
        .result-price {
            flex-shrink: 0;
            text-align: right;
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--primary-dark);
            white-space: nowrap;
        }
        .result-price small {
            display: block;
            font-size: 0.68rem;
            font-weight: 400;
            color: var(--text-light);
        }
        .search-no-results {
            padding: 1.5rem;
            text-align: center;
            color: var(--text-light);
            font-size: 0.9rem;
        }
        .search-loading {
            padding: 1.2rem;
            text-align: center;
            color: var(--primary);
            font-size: 0.9rem;
        }
        .search-ai-label {
            background: var(--bg-light);
            padding: 0.5rem 1.2rem;
            font-size: 0.75rem;
            color: var(--primary);
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        /* ─── HERO — IMAGE + WORDING + VIDEO BOX ─── */
        .hero {
            min-height: 100vh;
            position: relative;
            display: flex;
            align-items: center;
            background: var(--dark);
            padding: 8rem 5% 5rem;
            overflow: hidden;
        }
        .hero-bg {
            position: absolute;
            inset: 0;
            z-index: 0;
            background-size: cover;
            background-position: center;
        }
        .hero-overlay {
            position: absolute;
            inset: 0;
            z-index: 1;
            background: linear-gradient(100deg, rgba(20,12,10,0.92) 0%, rgba(20,12,10,0.78) 38%, rgba(20,12,10,0.45) 68%, rgba(20,12,10,0.25) 100%);
        }
        .hero-content {
            position: relative;
            z-index: 2;
            width: 100%;
            max-width: 1400px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1.05fr 0.95fr;
            gap: 3.5rem;
            align-items: center;
        }
        .hero-text { color: var(--white); }
        .hero-text h1 {
            font-size: clamp(2.3rem, 4.4vw, 3.6rem);
            color: var(--white);
            line-height: 1.14;
            margin-bottom: 1.3rem;
        }
        .hero-sub {
            font-size: 1.12rem;
            color: rgba(255,255,255,0.88);
            max-width: 520px;
            line-height: 1.7;
            margin-bottom: 2.2rem;
        }
        .hero-cta { display: flex; gap: 1rem; flex-wrap: wrap; }
        .hero-video-box {
            border-radius: 0;
            overflow: hidden;
            box-shadow: 0 25px 60px rgba(0,0,0,0.5);
            border: 2px solid #ffffff;
            aspect-ratio: 16 / 9;
            background: #000;
        }
        .hero-video-box video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
        @media (max-width: 900px) {
            .hero { padding: 7rem 5% 4rem; }
            .hero-content { grid-template-columns: 1fr; gap: 2rem; }
            .hero-video-box { max-width: 560px; width: 100%; margin: 0 auto; }
        }

        .btn-outline {
            border: 2px solid var(--white);
            color: var(--white);
            padding: 0.9rem 2rem;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }
        .btn-outline:hover { background: var(--white); color: var(--primary-dark); }

        .btn-gold {
            background: var(--accent);
            color: var(--text-dark);
            padding: 0.9rem 2rem;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-gold:hover { transform: translateY(-3px); }

        /* ─── GENERAL SECTION ─── */
        .section {
            padding: 6rem 5%;
            max-width: 1400px;
            margin: 0 auto;
        }
        .section-header { text-align: center; margin-bottom: 4rem; }
        .section-tag {
            color: var(--primary);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 3px;
            font-size: 0.85rem;
            display: block;
            margin-bottom: 0.75rem;
        }
        .section-title { font-size: clamp(2rem, 4vw, 3rem); color: var(--text-dark); margin-bottom: 1rem; }
        .section-subtitle { color: var(--text-light); max-width: 600px; margin: 0 auto; font-size: 1.05rem; }

        /* ─── PACKAGES (Kenyan) ─── */
        .packages-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem;
        }
        .package-card {
            background: var(--white);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            transition: all 0.35s ease;
            display: flex;
            flex-direction: column;
        }
        .package-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 50px rgba(0,0,0,0.15);
        }
        .package-image { position: relative; height: 230px; overflow: hidden; }
        .package-image img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.6s ease; }
        .package-card:hover .package-image img { transform: scale(1.08); }
        .package-badge {
            position: absolute;
            bottom: 12px;
            left: 12px;
            background: rgba(26,26,26,0.72);
            backdrop-filter: blur(4px);
            color: #fff;
            font-size: 0.72rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            padding: 0.28rem 0.7rem;
            border-radius: 20px;
            display: flex;
            align-items: center;
            gap: 0.3rem;
            pointer-events: none;
        }
        .package-content {
            padding: 1.75rem;
            display: flex;
            flex-direction: column;
            flex: 1;
        }
        .package-content > p {
            flex: 1;
        }
        .package-meta { display: flex; gap: 1rem; margin-bottom: 0.75rem; font-size: 0.85rem; color: var(--text-light); }
        .package-meta i { color: var(--primary); }
        .package-title { font-size: 1.35rem; margin-bottom: 0.75rem; color: var(--text-dark); }
        .package-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 1.25rem;
            padding-top: 1.25rem;
            border-top: 1px solid #eee;
        }
        .price { font-size: 1.4rem; font-weight: 700; color: var(--primary-dark); }
        .price span { font-size: 0.85rem; color: var(--text-light); font-weight: 400; }
        .btn-small {
            background: var(--primary-dark);
            color: var(--white);
            padding: 0.55rem 1.4rem;
            border-radius: 25px;
            text-decoration: none;
            font-size: 0.88rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .btn-small:hover { background: var(--primary); transform: translateX(4px); }

        /* ─── INTERNATIONAL OFFERS ─── */
        .intl-section {
            padding: 6rem 5%;
            background: var(--bg-light);
        }
        .intl-inner { max-width: 1400px; margin: 0 auto; }
        .intl-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }
        .intl-card {
            background: var(--white);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.07);
            display: flex;
            transition: all 0.35s ease;
            min-height: 180px;
        }
        .intl-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 20px 50px rgba(0,0,0,0.14);
        }
        .intl-card-image {
            width: 200px;
            flex-shrink: 0;
            position: relative;
            overflow: hidden;
            min-height: 180px;
        }
        .intl-card-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.6s ease;
        }
        .intl-card:hover .intl-card-image img { transform: scale(1.1); }
        .intl-card-badge {
            position: absolute;
            bottom: 12px;
            left: 12px;
            background: rgba(26,26,26,0.72);
            backdrop-filter: blur(4px);
            color: #fff;
            padding: 0.28rem 0.7rem;
            border-radius: 20px;
            font-size: 0.72rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            gap: 0.3rem;
            pointer-events: none;
        }
        .intl-card-content {
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            flex: 1;
            min-width: 0;
        }
        .intl-card-meta {
            display: flex;
            gap: 0.75rem;
            font-size: 0.8rem;
            color: var(--text-light);
            margin-bottom: 0.5rem;
            flex-wrap: wrap;
        }
        .intl-card-meta i { color: var(--primary); }
        .intl-card-title { font-size: 1.2rem; color: var(--text-dark); margin-bottom: 0.5rem; }
        .intl-card-desc { font-size: 0.88rem; color: var(--text-light); line-height: 1.6; margin-bottom: 0.75rem; flex: 1; }
        .intl-card-footer { display: flex; justify-content: space-between; align-items: center; }
        .intl-price { font-size: 1.25rem; font-weight: 700; color: var(--primary-dark); }
        .intl-price span { font-size: 0.8rem; color: var(--text-light); font-weight: 400; }

        /* ─── DESTINATIONS ─── */
        .destinations-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
        }
        .destination-card {
            position: relative;
            border-radius: 20px;
            overflow: hidden;
            height: 380px;
            cursor: pointer;
        }
        .destination-card img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.6s ease;
        }
        .destination-card:hover img { transform: scale(1.1); }
        .destination-overlay {
            position: absolute;
            bottom: 0; left: 0; right: 0;
            padding: 2rem;
            background: linear-gradient(to top, rgba(132, 30, 34, 0.95), transparent);
            color: var(--white);
            transition: all 0.3s ease;
        }
        .destination-card:hover .destination-overlay {
            background: linear-gradient(to top, rgba(132, 30, 34, 1), rgba(132, 30, 34, 0.65));
        }
        .destination-name { font-size: 1.65rem; margin-bottom: 0.5rem; }
        .destination-meta { display: flex; justify-content: space-between; align-items: center; font-size: 0.88rem; }
        .destination-tag {
            background: var(--accent);
            color: var(--text-dark);
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.78rem;
            font-weight: 600;
        }

        /* ─── SERVICES ─── */
        .services {
            background: var(--bg-light);
            position: relative;
            overflow: hidden;
        }
        .services::before {
            content: '';
            position: absolute;
            top: -50%; right: -10%;
            width: 500px; height: 500px;
            background: rgba(253, 176, 17, 0.08);
            border-radius: 50%;
            z-index: 0;
        }
        .services-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 2rem;
            position: relative;
            z-index: 1;
        }
        .service-card {
            text-align: center;
            padding: 0;
            background: var(--white);
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.05);
            transition: transform 0.3s ease;
            border-bottom: 4px solid transparent;
            display: flex;
            flex-direction: column;
            align-items: center;
            overflow: hidden;
        }
        .service-card:hover { transform: translateY(-10px); border-bottom-color: var(--accent); }
        .service-img {
            width: 100%;
            height: 180px;
            overflow: hidden;
            flex-shrink: 0;
        }
        .service-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
            display: block;
        }
        .service-card:hover .service-img img {
            transform: scale(1.08);
        }
        .service-card h3 { margin: 1.5rem 1.5rem 0.75rem; color: var(--primary-dark); font-size: 1.2rem; }
        .service-card p { color: var(--text-light); font-size: 0.92rem; line-height: 1.7; flex: 1; padding: 0 1.5rem 2rem; }

        /* ─── STATS ─── */
        .stats {
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
            color: var(--white);
            padding: 5rem 5%;
            position: relative;
            overflow: hidden;
        }
        .stats::before {
            content: '';
            position: absolute; inset: 0;
            background: url('https://images.unsplash.com/photo-1547471080-7cc2caa01a7e?auto=format&fit=crop&w=2000&q=80') center/cover;
            opacity: 0.08;
        }
        .stats-grid {
            max-width: 1200px; margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 3rem;
            text-align: center;
            position: relative; z-index: 1;
        }
        .stat-item h3 { font-size: 3rem; margin-bottom: 0.5rem; color: var(--accent); }
        .stat-item p { font-size: 1.05rem; opacity: 0.9; }

        /* ─── TESTIMONIALS ─── */
        .testimonials { background: var(--bg-light); }
        .testimonial-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem; }
        .testimonial-card {
            background: var(--white);
            padding: 2.5rem;
            border-radius: 20px;
            position: relative;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        }
        .testimonial-card::before {
            content: '"';
            font-family: 'Poppins', sans-serif;
            font-size: 6rem;
            position: absolute;
            top: -20px; left: 20px;
            color: var(--accent);
            opacity: 0.3;
            line-height: 1;
        }
        .testimonial-text { font-size: 1rem; line-height: 1.8; color: var(--text-dark); margin-bottom: 1.5rem; font-style: italic; }
        .testimonial-author { display: flex; align-items: center; gap: 1rem; }
        .author-avatar {
            width: 50px; height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            display: flex; align-items: center; justify-content: center;
            color: var(--white);
            font-weight: 600;
        }
        .author-info h4 { font-size: 1.05rem; margin-bottom: 0.2rem; }
        .author-info p { font-size: 0.85rem; color: var(--text-light); }

        /* ─── CTA ─── */
        .cta-section {
            background: linear-gradient(135deg, rgba(132, 30, 34, 0.95), rgba(160, 47, 29, 0.95)),
                        url('https://images.unsplash.com/photo-1476514525535-07fb3b4ae5f1?auto=format&fit=crop&w=2000&q=80') center/cover fixed;
            padding: 6rem 5%;
            text-align: center;
            color: var(--white);
        }
        .cta-content { max-width: 700px; margin: 0 auto; }
        .cta-content h2 { font-size: clamp(2rem, 4vw, 3rem); margin-bottom: 1.5rem; }
        .cta-content p { font-size: 1.15rem; margin-bottom: 2.5rem; opacity: 0.95; }

        /* ─── FOOTER & FLOATING — loaded via includes/footer.php ─── */

        /* ─── ANIMATIONS ─── */
        .fade-in {
            opacity: 0;
            transform: translateY(40px);
            transition: opacity 0.8s ease, transform 0.8s ease;
        }
        .fade-in.visible {
            opacity: 1;
            transform: translateY(0);
        }

        /* ─── RESPONSIVE ─── */
        @media (max-width: 1024px) {
            .packages-grid { grid-template-columns: repeat(2, 1fr); }
            .intl-grid { grid-template-columns: 1fr; }
            .services-grid { grid-template-columns: repeat(2, 1fr); }
        }

        @media (max-width: 768px) {
            .packages-grid { grid-template-columns: 1fr; }
            .services-grid { grid-template-columns: 1fr; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); gap: 2rem; }
            .intl-card { flex-direction: column; }
            .intl-card-image { width: 100%; height: 200px; }
        }
        /* Line clamp for card descriptions */
        .package-card .package-content > p,
        .intl-card-desc,
        .service-card p { display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }

    </style>
</head>
<body>

    <?php include 'includes/nav.php'; ?>


    <!-- ═══ HERO — IMAGE + WORDING + VIDEO BOX ═══ -->
    <section class="hero" id="home">
        <div class="hero-bg" style="background-image:url('<?= htmlspecialchars(getSetting('hero_image', 'uploads/hero/hero-bg.jpg')) ?>');"></div>
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <div class="hero-text fade-in">
                <h1><?= htmlspecialchars($heroTitle) ?></h1>
                <p class="hero-sub"><?= htmlspecialchars($heroSubtitle) ?></p>
                <div class="hero-cta">
                    <a href="/tours" class="btn-gold">Explore Tours</a>
                    <a href="/book" class="btn-outline">Plan My Trip</a>
                </div>
            </div>
            <div class="hero-video-box fade-in">
                <video
                    autoplay
                    loop
                    playsinline
                    controls
                    preload="metadata"
                    poster="uploads/hero/hero-poster.jpg"
                    title="Bamba Adventures Showreel">
                    <source src="uploads/hero/hero-bg.mp4" type="video/mp4">
                </video>
            </div>
        </div>
    </section>

    <!-- ═══ SEARCH BAR ═══ -->
    <div class="search-section">
        <div class="search-section-inner">
            <p>Search tours, safaris &amp; destinations</p>
            <div class="hero-search-wrap">
                <i class="fas fa-search search-icon"></i>
                <input type="text" id="search-input" placeholder="e.g. Maasai Mara, Paris, Beach Package…" autocomplete="off">
                <div id="search-results-dropdown"></div>
            </div>
        </div>
    </div>

    <!-- ═══ POPULAR KENYAN OFFERS ═══ -->
    <section class="section" id="packages">
        <div class="section-header fade-in">
            <span class="section-tag">Kenya Specialists</span>
            <h2 class="section-title">Popular Kenyan Offers</h2>
            <p class="section-subtitle">Hand-picked safari &amp; adventure experiences loved by our travellers across the Pearl of Africa</p>
        </div>

        <div class="packages-grid">
            <?php if (!empty($kenyanTours)): ?>
                <?php foreach (array_slice($kenyanTours, 0, 6) as $tour): ?>
                    <div class="package-card fade-in">
                        <div class="package-image">
                            <img src="<?= htmlspecialchars(tourImage($tour)) ?>" alt="<?= htmlspecialchars($tour['title']) ?>">
                            <?php if (!empty($tour['country_name']) || !empty($tour['destination_name'])): ?>
                            <span class="package-badge"><i class="fas fa-map-marker-alt" style="font-size:0.65rem;color:var(--accent)"></i> <?= htmlspecialchars($tour['country_name'] ?: $tour['destination_name']) ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="package-content">
                            <div class="package-meta">
                                <span><i class="far fa-clock"></i> <?= htmlspecialchars(tourDuration($tour)) ?></span>
                                <span><i class="far fa-user"></i> <?= htmlspecialchars(tourGroupSize($tour)) ?></span>
                            </div>
                            <h3 class="package-title"><?= htmlspecialchars($tour['title']) ?></h3>
                            <p><?= htmlspecialchars(strip_tags($tour['description'] ?: 'An amazing adventure awaits you with Bamba Adventures.')) ?></p>
                            <div class="package-footer">
                                <div class="price"><?= formatPrice($tour['price'], $tour['price_note'] ?? '') ?></div>
                                <a href="/tour/<?= htmlspecialchars($tour['slug']) ?>" class="btn-small">Details</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

    <!-- ═══ POPULAR INTERNATIONAL OFFERS ═══ -->
    <section class="intl-section" id="intl-offers">
        <div class="intl-inner">
            <div class="section-header fade-in">
                <span class="section-tag">Go Global</span>
                <h2 class="section-title">Popular International Offers</h2>
                <p class="section-subtitle">Extraordinary escapes beyond Kenya - curated packages to the world's most coveted destinations</p>
            </div>

            <div class="intl-grid">
                <?php if (!empty($intlTours)): ?>
                    <?php foreach (array_slice($intlTours, 0, 4) as $tour): ?>
                        <div class="intl-card fade-in">
                            <div class="intl-card-image">
                                <img src="<?= htmlspecialchars(tourImage($tour)) ?>" alt="<?= htmlspecialchars($tour['title']) ?>">
                                <?php if (!empty($tour['country_name']) || !empty($tour['destination_name'])): ?>
                                <span class="intl-card-badge"><i class="fas fa-map-marker-alt" style="font-size:0.65rem;color:var(--accent)"></i> <?= htmlspecialchars($tour['country_name'] ?: $tour['destination_name']) ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="intl-card-content">
                                <div>
                                    <div class="intl-card-meta">
                                        <span><i class="far fa-clock"></i> <?= htmlspecialchars(tourDuration($tour)) ?></span>
                                        <span><i class="far fa-user"></i> <?= htmlspecialchars(tourGroupSize($tour)) ?></span>
                                    </div>
                                    <h3 class="intl-card-title"><?= htmlspecialchars($tour['title']) ?></h3>
                                    <p class="intl-card-desc"><?= htmlspecialchars(strip_tags($tour['description'] ?: 'An amazing international adventure awaits.')) ?></p>
                                </div>
                                <div class="intl-card-footer">
                                    <div class="intl-price"><?= formatPrice($tour['price'], $tour['price_note'] ?? '') ?></div>
                                    <a href="/tour/<?= htmlspecialchars($tour['slug']) ?>" class="btn-small">Details</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- ═══ DESTINATIONS ═══ -->
    <section class="section" id="destinations">
        <div class="section-header fade-in">
            <span class="section-tag">Explore</span>
            <h2 class="section-title">Most Loved Destinations</h2>
            <p class="section-subtitle">Choose from our curated selection of breathtaking locations across continents</p>
        </div>

        <div class="destinations-grid">
            <?php if (!empty($featuredDestinations)): ?>
                <?php foreach (array_slice($featuredDestinations, 0, 6) as $dest): ?>
                    <a href="/destination/<?= htmlspecialchars($dest['slug']) ?>" class="destination-card fade-in" style="text-decoration:none;color:inherit;">
                        <img src="<?= htmlspecialchars($dest['image'] ?: 'https://images.unsplash.com/photo-1476514525535-07fb3b4ae5f1?auto=format&fit=crop&w=800&q=80') ?>" alt="<?= htmlspecialchars($dest['name']) ?>">
                        <div class="destination-overlay">
                            <h3 class="destination-name"><?= htmlspecialchars($dest['name']) ?></h3>
                            <div class="destination-meta">
                                <span><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($dest['description'] ? substr($dest['description'], 0, 30) . '...' : 'Explore') ?></span>
                                <span class="destination-tag">Discover</span>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

    <!-- ═══ SERVICES ═══ -->
    <section class="section services" id="services">
        <div class="section-header fade-in">
            <span class="section-tag">What We Offer</span>
            <h2 class="section-title">Adventure Tailored For You</h2>
            <p class="section-subtitle">Specialized experiences and travel services designed to create lasting memories</p>
        </div>

        <div class="services-grid">
            <?php if (!empty($featuredServices)): ?>
                <?php foreach (array_slice($featuredServices, 0, 4) as $svc): ?>
                    <div class="service-card fade-in">
                        <div class="service-img">
                                <img src="<?= htmlspecialchars($svc['image'] ?: 'https://images.unsplash.com/photo-1476514525535-07fb3b4ae5f1?auto=format&fit=crop&w=600&q=80') ?>" alt="<?= htmlspecialchars($svc['name']) ?>">
                            </div>
                        <h3><?= htmlspecialchars($svc['name']) ?></h3>
                        <p><?= htmlspecialchars($svc['description'] ?: 'Premium service tailored to your needs.') ?></p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

    <!-- ═══ STATS ═══ -->
    <section class="stats" id="about">
        <div class="stats-grid">
            <div class="stat-item fade-in"><h3>12+</h3><p>Years Experience</p></div>
            <div class="stat-item fade-in"><h3>5,000+</h3><p>Happy Travelers</p></div>
            <div class="stat-item fade-in"><h3>50+</h3><p>Destinations</p></div>
            <div class="stat-item fade-in"><h3>98%</h3><p>Satisfaction Rate</p></div>
        </div>
    </section>

    <!-- ═══ TESTIMONIALS ═══ -->
    <section class="section testimonials">
        <div class="section-header fade-in">
            <span class="section-tag">Testimonials</span>
            <h2 class="section-title">Traveler Stories</h2>
            <p class="section-subtitle">What our clients say about their adventures with us</p>
        </div>
        <div class="testimonial-grid">
            <?php if (!empty($testimonials)): ?>
                <?php foreach (array_slice($testimonials, 0, 3) as $t): ?>
                    <div class="testimonial-card fade-in">
                        <p class="testimonial-text"><?= htmlspecialchars($t['text']) ?></p>
                        <div class="testimonial-author">
                            <?php if (!empty($t['avatar'])): ?>
                                <img src="<?= htmlspecialchars($t['avatar']) ?>" alt="<?= htmlspecialchars($t['name']) ?>" style="width:50px;height:50px;border-radius:50%;object-fit:cover;">
                            <?php endif; ?>
                            <div class="author-info">
                                <h4><?= htmlspecialchars($t['name']) ?></h4>
                                <p><?= htmlspecialchars($t['role'] ?: ($t['tour_title'] ?: 'Happy Traveler')) ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

    <!-- ═══ CTA ═══ -->
    <section class="cta-section">
        <div class="cta-content fade-in">
            <h2>Ready to Start Your Adventure?</h2>
            <p>Let our experts craft your perfect itinerary. Custom trips available for groups, families, and solo travelers.</p>
            <a href="/book" class="btn-gold" style="font-size: 1.1rem; padding: 1rem 2.5rem;">Get Free Quote</a>
        </div>
    </section>

    <!-- ═══ SCRIPTS ═══ -->
    <script>
    /* ── Intersection observer fade-in ── */
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(e => { if (e.isIntersecting) e.target.classList.add('visible'); });
    }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });
    document.querySelectorAll('.fade-in').forEach(el => observer.observe(el));

    /* ── Live Search — data from database ── */
    const searchData = <?= json_encode($searchIndex, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

    const searchInput = document.getElementById('search-input');
    const searchDropdown = document.getElementById('search-results-dropdown');
    let searchTimeout;

    function performSearch(query) {
        const q = query.toLowerCase().trim();
        if (!q || q.length < 2) {
            searchDropdown.classList.remove('active');
            searchDropdown.innerHTML = '';
            return;
        }

        const localResults = searchData.filter(item => {
            return item.title.toLowerCase().includes(q) ||
                   item.type.toLowerCase().includes(q) ||
                   item.keywords.some(k => k.includes(q));
        });

        // Local results only. The previous version also fired a keyless request
        // to api.anthropic.com after 600ms, which could never succeed (no API
        // key, and a key cannot live in browser JS), delayed the results, and
        // sent every visitor's search term to a third party. Removed.
        renderResults(localResults, q);
    }

    function renderResults(results, query) {
        if (results.length === 0) {
            searchDropdown.innerHTML = `<div class="search-no-results"><i class="fas fa-search" style="margin-right:0.5rem;color:var(--primary)"></i> No results found for "<strong>${query}</strong>"</div>`;
            searchDropdown.classList.add('active');
            return;
        }
        let html = `<div class="search-ai-label"><i class="fas fa-bolt" style="margin-right:0.3rem"></i> Quick Results</div>`;
        results.slice(0, 6).forEach(item => {
            const imgHtml = item.img
                ? `<div class="search-result-img"><img src="${item.img}" alt="${item.title}" onerror="this.parentElement.innerHTML='<i class=\'fas fa-map-marker-alt\' style=\'color:var(--primary)\'></i>'"></div>`
                : `<div class="search-result-img"><i class="fas fa-map-marker-alt" style="color:var(--primary)"></i></div>`;

            const priceHtml = item.price
                ? `<div class="result-price">From $${Number(item.price).toLocaleString('en-US', {maximumFractionDigits:0})}${item.price_note ? `<small>${item.price_note}</small>` : ''}</div>`
                : '';

            html += `
                <div class="search-result-item" onclick="location.href='${item.url}';searchDropdown.classList.remove('active');searchInput.value=''">
                    ${imgHtml}
                    <div class="search-result-info">
                        <div class="result-title">${item.title}</div>
                        <div class="result-sub">${item.type}</div>
                        <span class="result-badge">${item.badge}</span>
                    </div>
                    ${priceHtml}
                </div>`;
        });
        searchDropdown.innerHTML = html;
        searchDropdown.classList.add('active');
    }


    searchInput.addEventListener('input', e => performSearch(e.target.value));
    searchInput.addEventListener('focus', e => { if (e.target.value.length >= 2) performSearch(e.target.value); });

    document.addEventListener('click', e => {
        if (!e.target.closest('.hero-search-wrap')) {
            searchDropdown.classList.remove('active');
        }
    });
    searchInput.addEventListener('keydown', e => {
        if (e.key === 'Escape') {
            searchDropdown.classList.remove('active');
            searchInput.value = '';
        }
    });

    /* ── Smooth scroll for anchors ── */
    document.querySelectorAll('a[href^="#"]').forEach(a => {
        a.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href === '#') return;
            const target = document.querySelector(href);
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

    /* ── Hero video: audio by default ── */
    (function() {
        const heroVideo = document.querySelector('.hero-video-box video');
        if (!heroVideo) return;

        // Never set the muted attribute; keep audio enabled.
        heroVideo.muted = false;

        const play = () => {
            const p = heroVideo.play();
            if (p) p.catch(() => {});
        };

        // Try unmuted autoplay first. If the browser blocks audio autoplay
        // (Chrome/Safari policy until user interaction), start muted so the
        // video still plays, then unmute automatically on the first user
        // interaction anywhere on the page.
        const autoUnmute = () => {
            heroVideo.muted = false;
            play();
            document.removeEventListener('pointerdown', autoUnmute);
            document.removeEventListener('keydown', autoUnmute);
            document.removeEventListener('touchstart', autoUnmute);
            document.removeEventListener('scroll', autoUnmute);
            document.removeEventListener('wheel', autoUnmute);
            document.removeEventListener('mousemove', autoUnmute);
        };

        const tryUnmuted = () => {
            heroVideo.muted = false;
            const p = heroVideo.play();
            if (p) p.catch(() => {
                // Browser blocked audio autoplay -> play muted, unmute on interaction
                heroVideo.muted = true;
                play();
                document.addEventListener('pointerdown', autoUnmute);
                document.addEventListener('keydown', autoUnmute);
                document.addEventListener('touchstart', autoUnmute);
                document.addEventListener('scroll', autoUnmute, { passive: true });
                document.addEventListener('wheel', autoUnmute, { passive: true });
                document.addEventListener('mousemove', autoUnmute, { passive: true });
            });
        };

        tryUnmuted();
        // Retry unmuted shortly after the video is ready (some browsers allow
        // audio autoplay once media is loaded, before any interaction).
        heroVideo.addEventListener('canplay', tryUnmuted, { once: true });
        setTimeout(tryUnmuted, 600);
        setTimeout(tryUnmuted, 1500);
    })();
    </script>

    <?php include __DIR__ . '/includes/footer.php'; ?>
