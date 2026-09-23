<?php
/**
 * BAMBA ADVENTURES — local dev router for `php -S`
 * Mirrors .htaccess rewrite rules so the site runs without Apache.
 * The homepage is index.php. (In the raw download from production it arrived
 * named index1.php; it has been renamed back here, and this fallback follows.)
 */
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$root = __DIR__;

// 1) Existing file or directory -> serve directly
if ($uri !== '/' && file_exists($root . $uri) && !is_dir($root . $uri)) {
    return false;
}

// 2) Trailing slash redirect (except dirs)
if ($uri !== '/' && substr($uri, -1) === '/' && !is_dir($root . $uri)) {
    header('Location: ' . rtrim($uri, '/'), true, 301);
    exit;
}

// 3) Pretty routes (mirror .htaccess)
$routes = [
    '#^/$#'                                => 'index.php',
    '#^/index\\.php$#'                     => 'index.php',
    '#^/tour/([^/]+)$#'                    => 'tours/index.php?slug=$1',
    '#^/tours$#'                           => 'pages/tours.php',
    '#^/tours/$#'                          => 'pages/tours.php',
    '#^/tours/category/([^/]+)$#'          => 'tours/category/index.php?slug=$1',
    '#^/destination/([^/]+)$#'             => 'destinations/index.php?slug=$1',
    '#^/service/([^/]+)$#'                 => 'services/index.php?slug=$1',
    '#^/country/([^/]+)$#'                 => 'countries/index.php?slug=$1',
    '#^/blog/post/([^/]+)$#'               => 'pages/blog-detail.php?slug=$1',
    '#^/blog$#'                            => 'pages/blog.php',
    '#^/page/([^/]+)$#'                    => 'pages/page.php?slug=$1',
    '#^/book$#'                            => 'pages/book.php',
    '#^/about$#'                           => 'pages/about.php',
    '#^/faq$#'                             => 'pages/faq.php',
    '#^/cancellation-policy$#'             => 'pages/cancellation.php',
    '#^/sitemap\.xml$#'                    => 'sitemap.php',
    '#^/admin$#'                            => 'admin/index.php',
    '#^/admin/$#'                           => 'admin/index.php',
];

foreach ($routes as $pattern => $target) {
    if (preg_match($pattern, $uri, $m)) {
        $base = preg_replace('/\?\s*slug=\$\d+/', '', $target);
        if (preg_match_all('/\$(\d+)/', $target, $refs)) {
            foreach ($refs[1] as $i) {
                $_GET['slug'] = $m[$i];
            }
        }
        $file = $root . '/' . $base;
        if (file_exists($file)) {
            chdir(dirname($file)); // mirror Apache: relative includes resolve from the script's dir
            require $file;
            exit;
        }
    }
}

// 4) Admin subpages: /admin/anything -> admin/anything.php
if (preg_match('#^/admin/([^/]+)$#', $uri, $m)) {
    $file = $root . '/admin/' . $m[1] . '.php';
    if (file_exists($file)) {
        chdir(dirname($file));
        require $file;
        exit;
    }
}

// 5) Nothing matched -> real 404.
//    Apache/LiteSpeed do NOT fall back to the homepage for an unmatched URL;
//    they return 404. This router used to serve the homepage here, which made
//    every invented URL look like a valid 200 page during local testing and
//    hid the difference between dev and production.
$fn = $root . '/includes/functions.php';
if (file_exists($fn)) {
    chdir($root);
    require_once $fn;
    render_not_found('Page Not Found', 'That page does not exist. Try the menu above, or start from the homepage.');
}
http_response_code(404);
header('Content-Type: text/plain; charset=utf-8');
echo '404 Not Found';
