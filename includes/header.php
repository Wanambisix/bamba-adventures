<?php
require_once __DIR__ . '/functions.php';
bamba_session_start();
security_headers(false);

$siteName = getSetting('site_name', 'Bamba Adventures');
$logo = getSetting('logo', '/assets/images/bamba-logo.png');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="/assets/images/favicon.ico" type="image/x-icon">
    <title><?php echo isset($pageTitle) ? esc($pageTitle) : esc(getSetting('meta_title_default', $siteName)); ?></title>
    <meta name="description" content="<?php echo isset($pageDescription) ? esc($pageDescription) : esc(getSetting('meta_description_default', 'Discover extraordinary journeys across Africa, Asia and the Middle East with Bamba Adventures — your trusted safari & tour partner.')); ?>">
<?php
/* ── SEO: build shared values ─────────────────────────────────── */
$_proto    = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$_host     = $_SERVER['HTTP_HOST'] ?? 'bambaadventures.co.ke';
$_siteUrl  = rtrim(getSetting('site_url', $_proto . '://' . $_host), '/');
$_reqPath  = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
$_canon    = isset($canonicalUrl)
    ? (strpos($canonicalUrl, 'http') === 0 ? $canonicalUrl : $_siteUrl . $canonicalUrl)
    : $_siteUrl . $_reqPath;
$_ogImage  = isset($pageOgImage) && $pageOgImage
    ? (strpos($pageOgImage, 'http') === 0 ? $pageOgImage : $_siteUrl . $pageOgImage)
    : $_siteUrl . '/assets/images/bamba-og.jpg';
$_ogTitle  = html_entity_decode(strip_tags(isset($pageTitle) ? $pageTitle : getSetting('meta_title_default', $siteName . ' | Premium African Safaris & Tours')), ENT_QUOTES, 'UTF-8');
$_ogDesc   = html_entity_decode(strip_tags(isset($pageDescription) ? $pageDescription : getSetting('meta_description_default', 'Discover extraordinary journeys across Africa, Asia and the Middle East with Bamba Adventures.')), ENT_QUOTES, 'UTF-8');
$_ogType   = $ogType ?? 'website';
?>
    <!-- Canonical & Robots -->
    <link rel="canonical" href="<?= esc($_canon) ?>">
    <meta name="robots" content="index, follow">

    <!-- Open Graph -->
    <meta property="og:type"         content="<?= esc($_ogType) ?>">
    <meta property="og:site_name"    content="<?= esc($siteName) ?>">
    <meta property="og:title"        content="<?= esc($_ogTitle) ?>">
    <meta property="og:description"  content="<?= esc($_ogDesc) ?>">
    <meta property="og:url"          content="<?= esc($_canon) ?>">
    <meta property="og:image"        content="<?= esc($_ogImage) ?>">
    <meta property="og:image:width"  content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:locale"       content="en_US">

    <!-- Twitter Card -->
    <meta name="twitter:card"        content="summary_large_image">
    <meta name="twitter:site"        content="@adventuresbamba">
    <meta name="twitter:title"       content="<?= esc($_ogTitle) ?>">
    <meta name="twitter:description" content="<?= esc($_ogDesc) ?>">
    <meta name="twitter:image"       content="<?= esc($_ogImage) ?>">

    <!-- JSON-LD: Organization (on every page) -->
    <script type="application/ld+json"><?php echo json_encode([
        '@context'  => 'https://schema.org',
        '@type'     => 'TravelAgency',
        'name'      => $siteName,
        'url'       => $_siteUrl,
        'logo'      => ['@type' => 'ImageObject', 'url' => $_siteUrl . '/assets/images/bamba-logo.png'],
        'image'     => $_ogImage,
        'telephone' => getSetting('contact_phone', '+254706606606'),
        'email'     => getSetting('contact_email', 'info@bambaadventures.co.ke'),
        'address'   => ['@type' => 'PostalAddress', 'streetAddress' => 'Ratanssi Educational Trust Building, 2nd Floor', 'addressLocality' => 'Nairobi', 'addressCountry' => 'KE'],
        'sameAs'    => ['https://www.facebook.com/bambaadventuresnevents/', 'https://x.com/adventuresbamba', 'https://www.instagram.com/bamba_adventures/', 'https://www.youtube.com/channel/UCzI138kTQQiKC7rZSBDYi7A'],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?></script>

<?php if (!empty($breadcrumbs)): ?>
    <!-- JSON-LD: Breadcrumb -->
    <script type="application/ld+json"><?php
    $_bcItems = [];
    foreach ($breadcrumbs as $i => $crumb) {
        $_bcItems[] = ['@type' => 'ListItem', 'position' => $i + 1, 'name' => $crumb['name'], 'item' => $_siteUrl . $crumb['url']];
    }
    echo json_encode(['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => $_bcItems], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    ?></script>
<?php endif; ?>

<?php if (!empty($seoTour)): ?>
    <!-- JSON-LD: Tour Package -->
    <script type="application/ld+json"><?php
    $_t     = $seoTour;
    $_imgs  = array_values(array_filter(json_decode($_t['images'] ?? '[]', true) ?: []));
    $_tData = [
        '@context'    => 'https://schema.org',
        '@type'       => 'TouristTrip',
        'name'        => $_t['title'],
        'description' => excerpt($_t['description'] ?? '', 300),
        'url'         => $_canon,
        'image'       => array_map(fn($img) => strpos($img, 'http') === 0 ? $img : $_siteUrl . $img, $_imgs),
        'touristType' => ['@type' => 'Audience', 'audienceType' => 'Adventure Travelers'],
    ];
    if (!empty($_t['price'])) {
        $_tData['offers'] = ['@type' => 'Offer', 'price' => (string)(float)$_t['price'], 'priceCurrency' => 'USD', 'availability' => 'https://schema.org/InStock', 'url' => $_canon];
    }
    if (!empty($_t['duration'])) {
        $_tData['itinerary'] = ['@type' => 'ItemList', 'name' => $_t['duration']];
    }
    echo json_encode($_tData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    ?></script>
<?php endif; ?>

<?php if (!empty($seoBlogPost)): ?>
    <!-- JSON-LD: Blog Post -->
    <script type="application/ld+json"><?php
    $_p    = $seoBlogPost;
    $_pImg = !empty($_p['image']) ? (strpos($_p['image'], 'http') === 0 ? $_p['image'] : $_siteUrl . $_p['image']) : $_ogImage;
    echo json_encode([
        '@context'         => 'https://schema.org',
        '@type'            => 'BlogPosting',
        'headline'         => $_p['title'],
        'description'      => excerpt($_p['excerpt'] ?? ($_p['content'] ?? ''), 155),
        'image'            => $_pImg,
        'url'              => $_canon,
        'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $_canon],
        'author'           => ['@type' => 'Person', 'name' => $_p['author'] ?? $siteName],
        'publisher'        => ['@type' => 'Organization', 'name' => $siteName, 'logo' => ['@type' => 'ImageObject', 'url' => $_siteUrl . '/assets/images/bamba-logo.png']],
        'datePublished'    => $_p['published_at'] ?? '',
        'dateModified'     => $_p['updated_at']   ?? ($_p['published_at'] ?? ''),
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    ?></script>
<?php endif; ?>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
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
        body { font-family: 'Poppins', sans-serif; color: var(--text-dark); line-height: 1.6; overflow-x: hidden; }
        h1, h2, h3, h4 { font-family: 'Poppins', sans-serif; font-weight: 700; }
    </style>
</head>
<body>

<?php include __DIR__ . '/nav.php'; ?>
