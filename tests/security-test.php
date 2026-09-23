<?php
/**
 * Security regression test.
 *
 *   php tests/security-test.php
 *
 * Exercises the hardening in includes/functions.php: CSRF tokens, the POST-only
 * action form, session cookie flags, escaping, rate limiting, and the image
 * upload rules. Needs no database - the DB-backed helpers are written to fail
 * open, and that is one of the things being asserted.
 *
 * Run it after touching functions.php, before deploying.
 */
$_SERVER['REMOTE_ADDR'] = '203.0.113.9';
$_SERVER['HTTPS'] = 'on';

require dirname(__DIR__) . '/includes/functions.php';

// start the session before anything is echoed, so cookie params can still be set
bamba_session_start();

$pass = 0; $fail = 0;
function t($label, $cond) {
    global $pass, $fail;
    if ($cond) { $pass++; echo "  PASS  $label\n"; }
    else       { $fail++; echo "  FAIL  $label\n"; }
}

echo "== CSRF ==\n";
$tok = csrf_token();
t('token is 64 hex chars', (bool) preg_match('/^[0-9a-f]{64}$/', $tok));
t('token is stable within a session', csrf_token() === $tok);
$field = csrf_field();
t('csrf_field renders a hidden input', str_contains($field, 'name="csrf_token"') && str_contains($field, $tok));

t('verify rejects a missing token', csrf_verify(false) === false);
$_POST['csrf_token'] = 'deadbeef';
t('verify rejects a wrong token', csrf_verify(false) === false);
$_POST['csrf_token'] = $tok;
t('verify accepts the right token', csrf_verify(false) === true);

echo "\n== action_form ==\n";
$af = action_form('/admin/x.php', ['action' => 'delete', 'id' => 7], 'btn btn-danger', 'fas fa-trash', 'Delete it?', 'Delete');
t('is a POST form', str_contains($af, 'method="post"'));
t('carries the token', str_contains($af, 'name="csrf_token"'));
t('carries the action and id', str_contains($af, 'value="delete"') && str_contains($af, 'name="id" value="7"'));
t('asks for confirmation', str_contains($af, 'confirm(&quot;Delete it?&quot;)'));
t('is not a link', !str_contains($af, '<a href'));

echo "\n== session hardening ==\n";
$p = session_get_cookie_params();
t('HttpOnly set', !empty($p['httponly']));
t('SameSite set', ($p['samesite'] ?? '') === 'Lax');
t('Secure set on https', !empty($p['secure']));

echo "\n== escaping ==\n";
t('esc() escapes quotes and tags',
  esc('<script>alert("x")</script>') === '&lt;script&gt;alert(&quot;x&quot;)&lt;/script&gt;');
t('esc() handles null', esc(null) === '');

echo "\n== rate limiting (no database configured) ==\n";
t('rate_limit fails open when DB is down', rate_limit(null, 'k', 1, 1) === true);
t('login_attempts_recent returns 0 when DB is down', login_attempts_recent(null, '1.2.3.4', 'admin') === 0);
t('login_attempt_record is a no-op without a DB', (function () { login_attempt_record(null, '1.2.3.4', 'admin'); return true; })());
t('login_attempts_clear is a no-op without a DB', (function () { login_attempts_clear(null, '1.2.3.4', 'admin'); return true; })());
t('client_ip() reads REMOTE_ADDR', client_ip() === '203.0.113.9');

echo "\n== upload validation (validate_image_upload) ==\n";
$txt = tempnam(sys_get_temp_dir(), 'up'); file_put_contents($txt, "<?php echo 'pwned';");
t('rejects a .php file by extension',
  validate_image_upload(['name' => 'shell.php', 'tmp_name' => $txt, 'size' => 20])['error'] === 'Invalid file type');
t('rejects a double extension (shell.php.jpg)',
  validate_image_upload(['name' => 'shell.php.jpg', 'tmp_name' => $txt, 'size' => 20])['error'] === 'Invalid file name');
t('rejects a .jpg whose bytes are not an image',
  validate_image_upload(['name' => 'photo.jpg', 'tmp_name' => $txt, 'size' => 20])['error'] === 'That file is not a valid image');
t('rejects an empty filename',
  validate_image_upload(['name' => '', 'tmp_name' => $txt, 'size' => 20])['error'] === 'No file uploaded');
t('rejects a file over the size limit',
  validate_image_upload(['name' => 'photo.jpg', 'tmp_name' => $txt, 'size' => 99999999], 5242880)['error'] === 'File too large (max 5MB)');

// a real 1x1 PNG, built in memory
$png = tempnam(sys_get_temp_dir(), 'png');
file_put_contents($png, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='));
$ok = validate_image_upload(['name' => 'Summer Photo 2026.PNG', 'tmp_name' => $png, 'size' => filesize($png)]);
t('accepts a real PNG', !isset($ok['error']) && $ok['ext'] === 'png');
t('derives the extension from the bytes, not the name', ($ok['ext'] ?? '') === 'png');
t('sanitises the slug', ($ok['slug'] ?? '') === 'summer-photo-2026');
unlink($png);
t('uploadImage refuses a non-HTTP upload',
  uploadImage(['name' => 'photo.jpg', 'tmp_name' => '/etc/passwd', 'size' => 20])['error'] === 'No file uploaded');
unlink($txt);

echo "\n== document uploads (career resumes) ==\n";
// NOTE for anyone editing these fixtures: the "PHP source" one must not contain
// "$_GET" or similar. On this Windows host Defender treats that as a webshell
// signature, quarantines the temp file, and the next read returns false - which
// makes the assertion fail with "Could not read that file" instead of the
// magic-byte error. Nothing to do with the code under test: the same bytes
// uploaded over real HTTP are rejected correctly.
function fixture($prefix, $content) {
    $p = tempnam(sys_get_temp_dir(), $prefix);
    $h = fopen($p, 'wb');
    fwrite($h, $content);
    fclose($h);
    return $p;
}

$php  = fixture('fixt', "<?php echo 'this is not a pdf at all'; ?>");
$pdf  = fixture('fixt', "%PDF-1.4\n1 0 obj\n<<>>\nendobj\ntrailer\n<<>>\n%%EOF\n");
$docx = fixture('fixt', "PK\x03\x04" . str_repeat("\x00", 60));
$doc  = fixture('fixt', "\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1" . str_repeat("\x00", 60));

t('rejects a .php by extension',
  validate_document_upload(['name' => 'shell.php', 'tmp_name' => $php, 'size' => 30])['error'] === 'Invalid file type. Please upload a PDF, DOC or DOCX.');
t('rejects PHP source renamed to .pdf',
  validate_document_upload(['name' => 'cv.pdf', 'tmp_name' => $php, 'size' => 30])['error'] === 'That file does not look like a valid PDF document.');
t('rejects a .docx whose bytes are not a zip',
  validate_document_upload(['name' => 'cv.docx', 'tmp_name' => $php, 'size' => 30])['error'] === 'That file does not look like a valid DOCX document.');
t('rejects an oversized resume',
  validate_document_upload(['name' => 'cv.pdf', 'tmp_name' => $pdf, 'size' => 6 * 1024 * 1024])['error'] === 'File too large (max 5MB)');
$r = validate_document_upload(['name' => 'My CV 2026.pdf', 'tmp_name' => $pdf, 'size' => filesize($pdf)]);
t('accepts a real PDF', !isset($r['error']) && $r['ext'] === 'pdf');
t('accepts a real .docx', (validate_document_upload(['name' => 'cv.docx', 'tmp_name' => $docx, 'size' => filesize($docx)])['ext'] ?? '') === 'docx');
t('accepts a real .doc', (validate_document_upload(['name' => 'cv.doc', 'tmp_name' => $doc, 'size' => filesize($doc)])['ext'] ?? '') === 'doc');
t('sanitises the resume slug', ($r['slug'] ?? '') === 'my-cv-2026');
t('uploadDocument refuses a non-HTTP upload',
  uploadDocument(['name' => 'cv.pdf', 'tmp_name' => $pdf, 'size' => 10])['error'] === 'No file uploaded');
unlink($php); unlink($pdf); unlink($docx); unlink($doc);

echo "\n== soft 404s (SEO) ==\n";
$root = dirname(__DIR__);
t('render_not_found() exists', function_exists('render_not_found'));
$controllers = ['tours/index.php', 'services/index.php', 'countries/index.php',
                'destinations/index.php', 'tours/category/index.php', 'pages/page.php'];
$redirectors = [];
foreach ($controllers as $c) {
    $src = file_get_contents($root . '/' . $c);
    // The old bug: a missing slug answered with a redirect to the homepage,
    // which search engines treat as a soft 404.
    if (preg_match('#header\(\s*[\'"]Location:\s*/#', $src)) $redirectors[] = $c;
}
t('no detail page redirects to the homepage on a missing slug', $redirectors === []);
if ($redirectors) echo '        still redirecting: ' . implode(', ', $redirectors) . "\n";

// The old dev router fell back to the homepage for any unmatched URL, which
// hid the dev/prod difference and made junk URLs return 200.
$router = file_get_contents($root . '/router.php');
t('dev router does not fall back to the homepage', !preg_match('#//\s*5\)\s*Fallback -> homepage#', $router));
t('dev router serves the homepage for /', (bool) preg_match("#'\#\^/\\\$\#'#", $router));
t('header.php supports a noindex meta', str_contains(file_get_contents($root . '/includes/header.php'), 'isset($robotsMeta)'));

echo "\n== uploads directory cannot execute code ==\n";
$uht = $root . '/uploads/.htaccess';
t('uploads/.htaccess exists', is_file($uht));
if (is_file($uht)) {
    $u = file_get_contents($uht);
    t('denies .php in uploads', (bool) preg_match('/FilesMatch.*php/i', $u));
    t('strips the PHP handler', str_contains($u, 'RemoveHandler'));
    t('avoids Options (which some hosts reject with a 500)', !preg_match('/^\s*Options\s/m', $u));
}

echo "\n== " . $pass . ' passed, ' . $fail . " failed ==\n";
exit($fail === 0 ? 0 : 1);
