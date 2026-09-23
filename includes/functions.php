<?php
// ================================================================
// BAMBA ADVENTURES - Helper Functions
// ================================================================

// config/database.php holds the credentials and is deliberately NOT in git
// (see README step 2). If it is missing - a fresh clone, or a half-finished
// install - carry on with $pdo = null so pages render their static fallback
// instead of dying with a blank 500.
$__dbConfig = __DIR__ . '/../config/database.php';
if (is_file($__dbConfig)) {
    require_once $__dbConfig;
} else {
    $pdo = null;
    $DB_HOST = $DB_NAME = $DB_USER = $DB_PASS = '';
}

// ----- SECURITY -----
function esc($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

// ----- SECURITY: session ---------------------------------------------------
// One place to start the session, so the cookie flags are always right.
// HttpOnly keeps the cookie away from JavaScript, SameSite=Lax blocks the
// cross-site POSTs that CSRF relies on, and Secure is set whenever the request
// is already HTTPS (production) but not on plain-HTTP local development.
function bamba_session_start() {
    if (session_status() !== PHP_SESSION_NONE) return;

    $https = (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off')
          || (strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https')
          || (int) ($_SERVER['SERVER_PORT'] ?? 0) === 443;

    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => $https,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// ----- SECURITY: CSRF ------------------------------------------------------
// Every state-changing request must carry this token. Without it, a logged-in
// admin who visits a malicious page can be made to delete records, change the
// admin password, or restore a hostile database dump.
function csrf_token() {
    bamba_session_start();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . esc(csrf_token()) . '">';
}

function csrf_verify($die = true) {
    bamba_session_start();
    $sent = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    $ok = !empty($_SESSION['csrf_token'])
        && is_string($sent)
        && hash_equals($_SESSION['csrf_token'], $sent);
    if (!$ok && $die) {
        http_response_code(419);
        exit('Security check failed. Reload the page and try again.');
    }
    return $ok;
}

// Renders a small POST form styled like the old action links, so destructive
// actions stop being GET requests that any <img> tag or link prefetch can fire.
function action_form($url, array $fields, $class, $icon, $confirm = '', $title = '') {
    // &quot; keeps this readable without nested quote escaping
    $confirmAttr = '';
    if ($confirm !== '') {
        $confirmAttr = ' onsubmit="return confirm(&quot;' . esc($confirm) . '&quot;)"';
    }
    $h = '<form method="post" action="' . esc($url) . '" class="inline-action"' . $confirmAttr . '>';
    $h .= csrf_field();
    foreach ($fields as $k => $v) {
        $h .= '<input type="hidden" name="' . esc($k) . '" value="' . esc($v) . '">';
    }
    $h .= '<button type="submit" class="' . esc($class) . '" title="'
        . esc($title !== '' ? $title : 'Submit') . '"><i class="' . esc($icon) . '"></i></button>';
    return $h . '</form>';
}

// ----- SECURITY: redirect + one-shot messages ------------------------------
function redirect($url) {
    header('Location: ' . $url);
    exit;
}

function flash($message, $type = 'success') {
    bamba_session_start();
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}

function take_flash() {
    bamba_session_start();
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

function render_flash() {
    $f = take_flash();
    if (!$f) return '';
    $class = ($f['type'] === 'error') ? 'flash flash-error' : 'flash flash-success';
    return '<div class="' . $class . '">' . esc($f['message']) . '</div>';
}

// ----- 404 -----------------------------------------------------------------
// Detail pages used to answer a missing slug with header('Location: /'), i.e.
// a 302 to the homepage. Search engines treat "redirect to the homepage" as a
// soft 404, so every invented URL (/tour/anything-at-all) returned a 200-class
// page and burnt crawl budget. This renders a real 404 with noindex instead,
// matching what pages/blog-detail.php already did correctly.
function render_not_found($title, $message = null, $links = true) {
    http_response_code(404);

    $pageTitle       = $title . ' | Bamba Adventures';
    $pageDescription = $message ?: 'The page you are looking for could not be found.';
    $robotsMeta      = 'noindex, follow';   // never index a 404

    // Callers may be either the shared-header pages or the standalone ones;
    // this always renders through the shared header/footer so the 404 looks
    // like the rest of the site.
    if (!headers_sent()) {
        header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
    }

    include __DIR__ . '/header.php';

    echo '<section style="max-width:900px;margin:0 auto;padding:9rem 5% 6rem;text-align:center;">';
    echo '<p style="font-family:var(--font-serif);font-size:4rem;line-height:1;color:var(--primary);margin-bottom:1rem;">404</p>';
    echo '<h1 style="font-family:var(--font-serif);font-size:clamp(1.6rem,3.5vw,2.4rem);color:var(--primary-dark);margin-bottom:1rem;">' . esc($title) . '</h1>';
    echo '<p style="color:var(--text-light);line-height:1.8;font-size:1.05rem;max-width:520px;margin:0 auto 2rem;">' . esc($pageDescription) . '</p>';
    if ($links) {
        echo '<p><a href="/" style="display:inline-block;padding:0.9rem 2rem;background:var(--primary);color:#fff;border-radius:50px;text-decoration:none;font-weight:600;margin:0 0.3rem 0.6rem;">Back to Homepage</a>';
        echo '<a href="/tours" style="display:inline-block;padding:0.9rem 2rem;border:2px solid var(--primary);color:var(--primary);border-radius:50px;text-decoration:none;font-weight:600;margin:0 0.3rem 0.6rem;">Browse Tours</a></p>';
    }
    echo '</section>';

    include __DIR__ . '/footer.php';
    exit;
}

// ----- SECURITY: response headers -----------------------------------------
function security_headers($isAdmin = false) {
    if (headers_sent()) return;
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
    if ($isAdmin) {
        // Admin pages must never be stored by a proxy or the browser cache.
        header('Cache-Control: no-store, no-cache, must-revalidate, private');
        header('Pragma: no-cache');
    }
}

// ----- SECURITY: generic rate limit ---------------------------------------
// Used by the public JSON API and the booking forms. Keyed on whatever the
// caller passes (usually action + client IP). Fails open if the DB is down, so
// a database problem never blocks genuine customers.
function rate_limit($pdo, $key, $max = 5, $minutes = 10) {
    if (!$pdo) return true;
    $max = (int) $max; $minutes = (int) $minutes;
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS rate_limits (
            id INT AUTO_INCREMENT PRIMARY KEY,
            rl_key VARCHAR(190) NOT NULL,
            created_at DATETIME NOT NULL,
            INDEX idx_key_time (rl_key, created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $pdo->exec("DELETE FROM rate_limits WHERE created_at < (NOW() - INTERVAL 1 DAY)");
        $s = $pdo->prepare("SELECT COUNT(*) FROM rate_limits
            WHERE rl_key = ? AND created_at > (NOW() - INTERVAL " . $minutes . " MINUTE)");
        $s->execute([$key]);
        if ((int) $s->fetchColumn() >= $max) return false;
        $pdo->prepare("INSERT INTO rate_limits (rl_key, created_at) VALUES (?, NOW())")->execute([$key]);
        return true;
    } catch (Exception $e) {
        return true;
    }
}

function client_ip() {
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

// ----- SECURITY: login throttling -----------------------------------------
// Five failures per IP or per username in a 15-minute window. Without this,
// the login form can be brute-forced as fast as the network allows.
const BAMBA_LOGIN_MAX_ATTEMPTS = 5;
const BAMBA_LOGIN_WINDOW_MIN   = 15;

function login_attempts_table($pdo) {
    static $done = false;
    if ($done || !$pdo) return;
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS login_attempts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            ip VARCHAR(45) NOT NULL,
            username VARCHAR(100) NOT NULL,
            attempted_at DATETIME NOT NULL,
            INDEX idx_ip_time (ip, attempted_at),
            INDEX idx_username_time (username, attempted_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    } catch (Exception $e) { /* the check below simply returns 0 */ }
    $done = true;
}

function login_attempts_recent($pdo, $ip, $username) {
    login_attempts_table($pdo);
    if (!$pdo) return 0;
    try {
        $s = $pdo->prepare("SELECT COUNT(*) FROM login_attempts
            WHERE (ip = ? OR username = ?) AND attempted_at > (NOW() - INTERVAL " . BAMBA_LOGIN_WINDOW_MIN . " MINUTE)");
        $s->execute([$ip, $username]);
        return (int) $s->fetchColumn();
    } catch (Exception $e) {
        return 0;
    }
}

function login_attempt_record($pdo, $ip, $username) {
    login_attempts_table($pdo);
    if (!$pdo) return;
    try {
        $pdo->prepare("INSERT INTO login_attempts (ip, username, attempted_at) VALUES (?, ?, NOW())")
            ->execute([$ip, $username]);
        // keep the table small
        $pdo->exec("DELETE FROM login_attempts WHERE attempted_at < (NOW() - INTERVAL 1 DAY)");
    } catch (Exception $e) { }
}

function login_attempts_clear($pdo, $ip, $username) {
    if (!$pdo) return;
    try {
        $pdo->prepare("DELETE FROM login_attempts WHERE ip = ? OR username = ?")->execute([$ip, $username]);
    } catch (Exception $e) { }
}

function slugify($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    return $text ?: 'item-' . time();
}

// ----- SETTINGS -----
function getSetting($key, $default = '') {
    global $pdo;
    if (!$pdo) return $default;
    try {
        $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $row = $stmt->fetch();
        return $row ? $row['setting_value'] : $default;
    } catch (Exception $e) {
        return $default;
    }
}

function setSetting($key, $value) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
    $stmt->execute([$key, $value, $value]);
}

// ----- NAVIGATION -----
function getDestinations($status = 'active', $featured = null) {
    global $pdo;
    if (!$pdo) return [];
    $sql = "SELECT * FROM destinations WHERE status = ?";
    $params = [$status];
    if ($featured !== null) {
        $sql .= " AND featured = ?";
        $params[] = $featured;
    }
    $sql .= " ORDER BY sort_order, name";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function getServices($status = 'active', $featured = null) {
    global $pdo;
    if (!$pdo) return [];
    $sql = "SELECT * FROM services WHERE status = ?";
    $params = [$status];
    if ($featured !== null) {
        $sql .= " AND featured = ?";
        $params[] = $featured;
    }
    $sql .= " ORDER BY sort_order, name";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function getTours($filters = []) {
    global $pdo;
    if (!$pdo) return [];
    $sql = "SELECT t.*, d.name as destination_name, d.slug as destination_slug, c.name as country_name, c.slug as country_slug FROM tours t LEFT JOIN destinations d ON t.destination_id = d.id LEFT JOIN countries c ON t.country_id = c.id WHERE 1=1";
    $params = [];
    if (!empty($filters['status'])) {
        $sql .= " AND t.status = ?";
        $params[] = $filters['status'];
    }
    if (!empty($filters['featured'])) {
        $sql .= " AND t.featured = ?";
        $params[] = $filters['featured'];
    }
    if (!empty($filters['destination_id'])) {
        $sql .= " AND t.destination_id = ?";
        $params[] = $filters['destination_id'];
    }
    if (!empty($filters['country_id'])) {
        $sql .= " AND t.country_id = ?";
        $params[] = $filters['country_id'];
    }
    if (!empty($filters['category_slug'])) {
        $sql .= " AND t.id IN (SELECT l.tour_id FROM tour_category_links l JOIN tour_categories tc ON l.category_id = tc.id WHERE tc.slug = ?)";
        $params[] = $filters['category_slug'];
    }
    if (!empty($filters['search'])) {
        $sql .= " AND (t.title LIKE ? OR t.description LIKE ?)";
        $params[] = '%' . $filters['search'] . '%';
        $params[] = '%' . $filters['search'] . '%';
    }
    $sql .= " ORDER BY t.created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function getTourBySlug($slug) {
    global $pdo;
    if (!$pdo) return null;
    $stmt = $pdo->prepare("SELECT t.*, d.name as destination_name, d.slug as destination_slug, c.name as country_name, c.slug as country_slug FROM tours t LEFT JOIN destinations d ON t.destination_id = d.id LEFT JOIN countries c ON t.country_id = c.id WHERE t.slug = ? AND t.status = 'active'");
    $stmt->execute([$slug]);
    return $stmt->fetch();
}

function getDestinationBySlug($slug) {
    global $pdo;
    if (!$pdo) return null;
    $stmt = $pdo->prepare("SELECT * FROM destinations WHERE slug = ? AND status = 'active'");
    $stmt->execute([$slug]);
    return $stmt->fetch();
}

function getServiceBySlug($slug) {
    global $pdo;
    if (!$pdo) return null;
    $stmt = $pdo->prepare("SELECT * FROM services WHERE slug = ? AND status = 'active'");
    $stmt->execute([$slug]);
    return $stmt->fetch();
}

function getCountries($destinationId = null, $status = 'active') {
    global $pdo;
    if (!$pdo) return [];
    $sql = "SELECT c.*, d.name as destination_name, d.slug as destination_slug FROM countries c LEFT JOIN destinations d ON c.destination_id = d.id WHERE 1=1";
    $params = [];
    if ($destinationId) {
        $sql .= " AND c.destination_id = ?";
        $params[] = $destinationId;
    }
    if ($status) {
        $sql .= " AND c.status = ?";
        $params[] = $status;
    }
    $sql .= " ORDER BY c.sort_order, c.name";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function getCountryBySlug($slug) {
    global $pdo;
    if (!$pdo) return null;
    $stmt = $pdo->prepare("SELECT c.*, d.name as destination_name, d.slug as destination_slug FROM countries c LEFT JOIN destinations d ON c.destination_id = d.id WHERE c.slug = ? AND c.status = 'active'");
    $stmt->execute([$slug]);
    return $stmt->fetch();
}

function getTourCategories($status = 'active') {
    global $pdo;
    if (!$pdo) return [];
    $sql = "SELECT * FROM tour_categories WHERE status = ? ORDER BY sort_order, name";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$status]);
    return $stmt->fetchAll();
}

function getTourCategoryBySlug($slug) {
    global $pdo;
    if (!$pdo) return null;
    $stmt = $pdo->prepare("SELECT * FROM tour_categories WHERE slug = ? AND status = 'active'");
    $stmt->execute([$slug]);
    return $stmt->fetch();
}

function getTourCategoriesForTour($tourId) {
    global $pdo;
    if (!$pdo) return [];
    $stmt = $pdo->prepare("SELECT c.* FROM tour_categories c JOIN tour_category_links l ON c.id = l.category_id WHERE l.tour_id = ? AND c.status = 'active' ORDER BY c.sort_order, c.name");
    $stmt->execute([$tourId]);
    return $stmt->fetchAll();
}

function setTourCategories($tourId, $categoryIds) {
    global $pdo;
    if (!$pdo) return;
    $pdo->prepare("DELETE FROM tour_category_links WHERE tour_id = ?")->execute([$tourId]);
    foreach (array_filter($categoryIds) as $catId) {
        $pdo->prepare("INSERT IGNORE INTO tour_category_links (tour_id, category_id) VALUES (?, ?)") ->execute([$tourId, $catId]);
    }
}

function getToursByCategorySlug($slug, $limit = null) {
    global $pdo;
    if (!$pdo) return [];
    $sql = "SELECT t.*, d.name as destination_name, d.slug as destination_slug, c.name as country_name, c.slug as country_slug FROM tours t LEFT JOIN destinations d ON t.destination_id = d.id LEFT JOIN countries c ON t.country_id = c.id JOIN tour_category_links l ON t.id = l.tour_id JOIN tour_categories tc ON l.category_id = tc.id WHERE t.status = 'active' AND tc.slug = ?";
    $params = [$slug];
    $sql .= " ORDER BY t.created_at DESC";
    if ($limit) $sql .= " LIMIT " . (int)$limit;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function getTestimonials($limit = null) {
    global $pdo;
    if (!$pdo) return [];
    $sql = "SELECT t.*, tr.title as tour_title FROM testimonials t LEFT JOIN tours tr ON t.tour_id = tr.id WHERE t.status = 'active' ORDER BY t.sort_order, t.created_at DESC";
    if ($limit) $sql .= " LIMIT " . (int)$limit;
    return $pdo->query($sql)->fetchAll();
}

function getBlogPosts($limit = null, $status = 'published') {
    global $pdo;
    if (!$pdo) return [];
    $sql = "SELECT * FROM blog_posts WHERE status = ? ORDER BY published_at DESC, created_at DESC";
    if ($limit) $sql .= " LIMIT " . (int)$limit;
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$status]);
    return $stmt->fetchAll();
}

function getBlogBySlug($slug) {
    global $pdo;
    if (!$pdo) return null;
    $stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE slug = ? AND status = 'published'");
    $stmt->execute([$slug]);
    return $stmt->fetch();
}

function getFaqs($category = null) {
    global $pdo;
    if (!$pdo) return [];
    if ($category) {
        $stmt = $pdo->prepare("SELECT * FROM faqs WHERE status = 'active' AND category = ? ORDER BY sort_order");
        $stmt->execute([$category]);
        return $stmt->fetchAll();
    }
    return $pdo->query("SELECT * FROM faqs WHERE status = 'active' ORDER BY sort_order")->fetchAll();
}

function getCareers($status = 'active') {
    global $pdo;
    if (!$pdo) return [];
    $stmt = $pdo->prepare("SELECT * FROM careers WHERE status = ? ORDER BY created_at DESC");
    $stmt->execute([$status]);
    return $stmt->fetchAll();
}

function getCareerById($id) {
    global $pdo;
    if (!$pdo) return null;
    $stmt = $pdo->prepare("SELECT * FROM careers WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

// ----- STATS -----
function getCounts() {
    global $pdo;
    if (!$pdo) return [];
    return [
        'tours' => $pdo->query("SELECT COUNT(*) FROM tours WHERE status = 'active'")->fetchColumn(),
        'destinations' => $pdo->query("SELECT COUNT(*) FROM destinations WHERE status = 'active'")->fetchColumn(),
        'bookings' => $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'new'")->fetchColumn(),
        'inquiries' => $pdo->query("SELECT COUNT(*) FROM inquiries WHERE status = 'new'")->fetchColumn(),
        'subscribers' => $pdo->query("SELECT COUNT(*) FROM subscribers WHERE status = 'active'")->fetchColumn(),
        'testimonials' => $pdo->query("SELECT COUNT(*) FROM testimonials WHERE status = 'active'")->fetchColumn(),
        'blog_posts' => $pdo->query("SELECT COUNT(*) FROM blog_posts WHERE status = 'published'")->fetchColumn(),
    ];
}

// ----- ADMIN AUTH -----
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']) && $_SESSION['admin_id'];
}

function requireAdmin() {
    bamba_session_start();
    security_headers(true);
    csrf_token();          // make sure every admin page has a token to render
    if (!isAdminLoggedIn()) {
        header('Location: /admin/index.php');
        exit;
    }
}

// ----- UPLOAD -----
// Validates an uploaded image and returns either ['error' => ...] or
// ['ext' => ..., 'slug' => ...]. Kept separate from the move so the rules can be
// tested without a real HTTP upload.
function validate_image_upload($file, $maxBytes = 5242880) {
    if (empty($file['name'])) {
        return ['error' => 'No file uploaded'];
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (!in_array($ext, $allowed, true)) return ['error' => 'Invalid file type'];

    // Reject names that hide a second, executable extension (shell.php.jpg).
    $base = strtolower(pathinfo($file['name'], PATHINFO_FILENAME));
    if (preg_match('/\.(php|phtml|phar|php[0-9]|htaccess|pl|py|cgi|sh|asp|aspx|jsp)$/', $base)) {
        return ['error' => 'Invalid file name'];
    }

    if (($file['size'] ?? 0) > $maxBytes) return ['error' => 'File too large (max 5MB)'];

    // The extension is only a claim. Check the actual bytes.
    $byMime = ['image/jpeg' => 'jpg', 'image/png' => 'png',
               'image/gif' => 'gif', 'image/webp' => 'webp'];
    $mime = null;
    if (!empty($file['tmp_name']) && is_file($file['tmp_name'])) {
        $info = @getimagesize($file['tmp_name']);
        if ($info !== false) $mime = $info['mime'];
    }
    // A caller may pass a claimed mime (tests, or an API client).
    if ($mime === null && !empty($file['mime'])) $mime = $file['mime'];
    if ($mime === null || !isset($byMime[$mime])) {
        return ['error' => 'That file is not a valid image'];
    }

    return ['ext' => $byMime[$mime],
            'slug' => trim(preg_replace('/[^a-z0-9]+/', '-', $base), '-')];
}

function uploadImage($file, $subdir = '') {
    $uploadDir = __DIR__ . '/../uploads/' . ($subdir ? $subdir . '/' : '');
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    if (empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        return ['error' => 'No file uploaded'];
    }

    $v = validate_image_upload($file);
    if (isset($v['error'])) return $v;

    // Filename: random prefix + a short sanitised slug. The extension comes from
    // the verified MIME type, never from what was uploaded.
    $filename = bin2hex(random_bytes(6))
              . ($v['slug'] !== '' ? '_' . substr($v['slug'], 0, 60) : '')
              . '.' . $v['ext'];
    $path = $uploadDir . $filename;

    if (move_uploaded_file($file['tmp_name'], $path)) {
        return ['success' => true, 'path' => 'uploads/' . ($subdir ? $subdir . '/' : '') . $filename];
    }
    return ['error' => 'Upload failed'];
}

// Documents (career-application resumes). Same shape as validate_image_upload:
// the extension is only a claim, so the leading bytes are checked against it.
// Previously the API trusted the extension alone and had no size limit at all.
function validate_document_upload($file, $maxBytes = 5242880) {
    if (empty($file['name'])) return ['error' => 'No file uploaded'];

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['pdf', 'doc', 'docx'];
    if (!in_array($ext, $allowed, true)) {
        return ['error' => 'Invalid file type. Please upload a PDF, DOC or DOCX.'];
    }

    $base = strtolower(pathinfo($file['name'], PATHINFO_FILENAME));
    if (preg_match('/\.(php|phtml|phar|php[0-9]|htaccess|pl|py|cgi|sh|asp|aspx|jsp)$/', $base)) {
        return ['error' => 'Invalid file name'];
    }

    if (($file['size'] ?? 0) > $maxBytes) return ['error' => 'File too large (max 5MB)'];

    $sig = null;
    if (!empty($file['tmp_name']) && is_file($file['tmp_name'])) {
        $fh = @fopen($file['tmp_name'], 'rb');
        if ($fh) { $sig = fread($fh, 8); fclose($fh); }
    }
    if (!is_string($sig) || $sig === '') return ['error' => 'Could not read that file'];

    $isPdf = strncmp($sig, '%PDF', 4) === 0;
    $isZip = strncmp($sig, "PK\x03\x04", 4) === 0;                          // .docx / OOXML
    $isOle = strncmp($sig, "\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1", 8) === 0;   // legacy .doc

    $matches = ($ext === 'pdf' && $isPdf) || ($ext === 'doc' && $isOle) || ($ext === 'docx' && $isZip);
    if (!$matches) {
        return ['error' => 'That file does not look like a valid ' . strtoupper($ext) . ' document.'];
    }

    return ['ext' => $ext, 'slug' => trim(preg_replace('/[^a-z0-9]+/', '-', $base), '-')];
}

function uploadDocument($file, $subdir = 'resumes') {
    $uploadDir = __DIR__ . '/../uploads/' . ($subdir ? $subdir . '/' : '');
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    if (empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        return ['error' => 'No file uploaded'];
    }

    $v = validate_document_upload($file);
    if (isset($v['error'])) return $v;

    $filename = bin2hex(random_bytes(6))
              . ($v['slug'] !== '' ? '_' . substr($v['slug'], 0, 60) : '')
              . '.' . $v['ext'];

    if (move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
        return ['success' => true, 'path' => 'uploads/' . ($subdir ? $subdir . '/' : '') . $filename];
    }
    return ['error' => 'Upload failed'];
}

// ----- FORMATTING -----
function formatPrice($price, $note = '') {
    if (!$price) return 'Contact for price';
    $formatted = 'From $' . number_format($price, 0);
    if ($note) $formatted .= ' <span class="price-note">' . esc($note) . '</span>';
    return $formatted;
}

function excerpt($text, $length = 150) {
    $text = strip_tags($text);
    if (strlen($text) <= $length) return $text;
    return substr($text, 0, $length) . '...';
}

function truncate($text, $length = 150) {
    return excerpt($text, $length);
}

function formatDate($date) {
    if (!$date) return '';
    return date('F j, Y', strtotime($date));
}

function starsHtml($rating) {
    $html = '';
    for ($i = 1; $i <= 5; $i++) {
        $html .= $i <= $rating 
            ? '<i class="fas fa-star" style="color:var(--accent)"></i>' 
            : '<i class="far fa-star" style="color:#ccc"></i>';
    }
    return $html;
}

function firstImage($imagesJson) {
    if (!$imagesJson) return '';
    $arr = json_decode($imagesJson, true);
    return is_array($arr) && !empty($arr) ? $arr[0] : '';
}


// ----- CMS PAGES -----
function getPages($status = 'active') {
    global $pdo;
    if (!$pdo) return [];
    try {
        $stmt = $pdo->prepare("SELECT * FROM pages WHERE status = ? ORDER BY sort_order, title");
        $stmt->execute([$status]);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        return [];
    }
}

function getPageBySlug($slug) {
    global $pdo;
    if (!$pdo) return null;
    try {
        $stmt = $pdo->prepare("SELECT * FROM pages WHERE slug = ? AND status = 'active' LIMIT 1");
        $stmt->execute([$slug]);
        return $stmt->fetch();
    } catch (Exception $e) {
        return null;
    }
}
