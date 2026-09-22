<?php
/**
 * Bamba Adventures — Dynamic XML Sitemap
 * Accessible at: /sitemap.xml  (routed via .htaccess)
 */
require_once __DIR__ . '/includes/functions.php';

header('Content-Type: application/xml; charset=utf-8');
header('X-Robots-Tag: noindex');

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$siteUrl  = rtrim(getSetting('site_url', $protocol . '://' . ($_SERVER['HTTP_HOST'] ?? 'bambaadventures.co.ke')), '/');
$today    = date('Y-m-d');

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

/* ── Helper ─────────────────────────────────────────────────── */
function sm_url($loc, $changefreq = 'monthly', $priority = '0.5', $lastmod = null) {
    echo "  <url>\n";
    echo "    <loc>" . htmlspecialchars($loc, ENT_XML1, 'UTF-8') . "</loc>\n";
    if ($lastmod) echo "    <lastmod>" . htmlspecialchars($lastmod, ENT_XML1, 'UTF-8') . "</lastmod>\n";
    echo "    <changefreq>{$changefreq}</changefreq>\n";
    echo "    <priority>{$priority}</priority>\n";
    echo "  </url>\n";
}

/* ── Static pages ───────────────────────────────────────────── */
sm_url($siteUrl . '/',                    'weekly',  '1.0', $today);
sm_url($siteUrl . '/tours',              'weekly',  '0.9', $today);
sm_url($siteUrl . '/about',             'monthly', '0.7', $today);
sm_url($siteUrl . '/blog',              'weekly',  '0.8', $today);
sm_url($siteUrl . '/faq',              'monthly', '0.6', $today);
sm_url($siteUrl . '/book',             'monthly', '0.7', $today);
sm_url($siteUrl . '/cancellation-policy', 'monthly', '0.5', $today);

/* ── Tours ──────────────────────────────────────────────────── */
try {
    $tours = getTours(['status' => 'active']);
    foreach ($tours as $t) {
        $lastmod = !empty($t['updated_at']) ? date('Y-m-d', strtotime($t['updated_at'])) : $today;
        sm_url($siteUrl . '/tour/' . $t['slug'], 'weekly', '0.9', $lastmod);
    }
} catch (Exception $e) {}

/* ── Destinations ───────────────────────────────────────────── */
try {
    $destinations = getDestinations();
    foreach ($destinations as $d) {
        sm_url($siteUrl . '/destination/' . $d['slug'], 'monthly', '0.7', $today);
    }
} catch (Exception $e) {}

/* ── Countries ──────────────────────────────────────────────── */
try {
    $countries = getCountries();
    foreach ($countries as $c) {
        if (!empty($c['slug'])) {
            sm_url($siteUrl . '/country/' . $c['slug'], 'monthly', '0.6', $today);
        }
    }
} catch (Exception $e) {}

/* ── Tour Categories ────────────────────────────────────────── */
try {
    $cats = getTourCategories();
    foreach ($cats as $cat) {
        sm_url($siteUrl . '/tours/category/' . $cat['slug'], 'weekly', '0.7', $today);
    }
} catch (Exception $e) {}

/* ── Blog posts ─────────────────────────────────────────────── */
try {
    $posts = getBlogPosts(500);
    foreach ($posts as $p) {
        $lastmod = !empty($p['published_at']) ? date('Y-m-d', strtotime($p['published_at'])) : $today;
        sm_url($siteUrl . '/blog/post/' . $p['slug'], 'monthly', '0.6', $lastmod);
    }
} catch (Exception $e) {}

/* ── CMS Pages ──────────────────────────────────────────────── */
try {
    global $pdo;
    // $pdo is null when the database is unreachable. Calling a method on null is
    // a fatal Error, and Error is not an Exception - which is why this used to
    // produce "Call to a member function query() on null" in the server log and
    // a half-written sitemap. Guard, and catch Throwable as well.
    if ($pdo) {
        $stmt = $pdo->query("SELECT slug, updated_at FROM pages WHERE status = 'active' ORDER BY sort_order");
        foreach ($stmt->fetchAll() as $p) {
            $lastmod = !empty($p['updated_at']) ? date('Y-m-d', strtotime($p['updated_at'])) : $today;
            sm_url($siteUrl . '/page/' . $p['slug'], 'monthly', '0.5', $lastmod);
        }
    }
} catch (Throwable $e) {}

echo '</urlset>';
