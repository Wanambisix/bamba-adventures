<?php
require_once __DIR__ . '/../includes/functions.php';
bamba_session_start();
security_headers(true);

// Database required for admin
if (!$pdo) {
    echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Admin Error</title><style>body{font-family:Inter,sans-serif;background:#841e22;color:#fff;display:flex;align-items:center;justify-content:center;height:100vh;margin:0;text-align:center;} .box{background:#fff;color:#333;padding:2.5rem;border-radius:16px;max-width:420px;} h1{margin:0 0 1rem;font-size:1.3rem;} p{margin:0 0 1.5rem;color:#666;line-height:1.6;} a{color:#841e22;font-weight:600;}</style></head><body><div class="box"><h1>Database Offline</h1><p>The admin panel requires a database connection.<br>Please check your database settings in <code>config/database.php</code> and ensure MySQL is running.</p><a href="/">Back to Website</a></div></body></html>';
    exit;
}

// Seed default admin if table is empty
$seedMessage = '';
try {
    $adminCount = $pdo->query("SELECT COUNT(*) FROM admins")->fetchColumn();
    if ($adminCount == 0) {
        // Generate a random bootstrap password and show it once. This file is
        // in version control, so a hard-coded default would be public knowledge.
        $bootstrapPassword = bin2hex(random_bytes(6));
        $pdo->prepare("INSERT INTO admins (username, password_hash, name, role) VALUES (?, ?, ?, ?)")
            ->execute(['admin', password_hash($bootstrapPassword, PASSWORD_DEFAULT), 'Administrator', 'super']);
        $seedMessage = 'First-time setup complete. Login with username: <strong>admin</strong> and password: <strong>' . htmlspecialchars($bootstrapPassword) . '</strong>. Please change your password after logging in.';
    }
} catch (PDOException $e) {
    echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Admin Error</title><style>body{font-family:Inter,sans-serif;background:#841e22;color:#fff;display:flex;align-items:center;justify-content:center;height:100vh;margin:0;text-align:center;} .box{background:#fff;color:#333;padding:2.5rem;border-radius:16px;max-width:420px;} h1{margin:0 0 1rem;font-size:1.3rem;} p{margin:0 0 1.5rem;color:#666;line-height:1.6;} code{background:#f5f5f5;padding:0.15rem 0.4rem;border-radius:4px;font-size:0.85rem;} a{color:#841e22;font-weight:600;}</style></head><body><div class="box"><h1>Database Tables Missing</h1><p>The admin panel database tables have not been created yet.<br><br>Please run the database setup script or import the SQL schema to create the required tables.</p><a href="/">Back to Website</a></div></body></html>';
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

    // Throttle: 5 failures per IP or username per 15 minutes.
    if (login_attempts_recent($pdo, $ip, $username) >= BAMBA_LOGIN_MAX_ATTEMPTS) {
        $error = 'Too many failed attempts. Please wait ' . BAMBA_LOGIN_WINDOW_MIN
               . ' minutes before trying again.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
            $stmt->execute([$username]);
            $admin = $stmt->fetch();

            if ($admin && password_verify($password, $admin['password_hash'])) {
                login_attempts_clear($pdo, $ip, $username);

                // New session id on privilege change - stops session fixation,
                // where an attacker plants a known id before you log in.
                session_regenerate_id(true);

                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_name'] = $admin['name'];
                $_SESSION['admin_role'] = $admin['role'];

                $pdo->prepare("UPDATE admins SET last_login = NOW() WHERE id = ?")
                    ->execute([$admin['id']]);

                redirect('/admin/dashboard.php');
            } else {
                login_attempt_record($pdo, $ip, $username);
                // Deliberately identical for unknown user and wrong password.
                $error = 'Invalid username or password';
            }
        } catch (PDOException $e) {
            $error = 'Database error. Please try again later.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | Bamba Adventures</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: linear-gradient(135deg, #841e22 0%, #a02f1d 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-card { background: #fff; border-radius: 20px; padding: 3rem; width: 100%; max-width: 420px; box-shadow: 0 25px 60px rgba(0,0,0,0.3); }
        .login-card .logo { height: 55px; margin-bottom: 1.5rem; display: block; margin-left: auto; margin-right: auto; }
        .login-card h1 { font-size: 1.5rem; font-weight: 700; color: #1a1a1a; text-align: center; margin-bottom: 0.5rem; }
        .login-card p { color: #666; text-align: center; margin-bottom: 2rem; font-size: 0.9rem; }
        .form-group { margin-bottom: 1.25rem; }
        .form-group label { display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.4rem; color: #333; }
        .form-group input { width: 100%; padding: 0.85rem 1rem; border: 1.5px solid #e0e0e0; border-radius: 10px; font-family: inherit; font-size: 0.95rem; outline: none; transition: all 0.3s; }
        .form-group input:focus { border-color: #841e22; box-shadow: 0 0 0 3px rgba(132,30,34,0.1); }
        .btn-login { width: 100%; background: #841e22; color: #fff; border: none; padding: 0.9rem; border-radius: 10px; font-size: 1rem; font-weight: 600; cursor: pointer; transition: all 0.3s; }
        .btn-login:hover { background: #a02f1d; transform: translateY(-1px); }
        .alert { background: #fee2e2; color: #991b1b; padding: 0.75rem 1rem; border-radius: 8px; font-size: 0.85rem; margin-bottom: 1rem; }
    </style>
</head>
<body>
    <div class="login-card">
        <img src="/assets/images/bamba-logo.png" alt="Bamba Adventures" class="logo">
        <h1>Admin Login</h1>
        <p>Sign in to manage your website</p>
        <?php if ($seedMessage): ?>
        <div class="alert" style="background:#d1fae5;color:#065f46;"><i class="fas fa-info-circle"></i> <?php echo $seedMessage; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
        <div class="alert"><i class="fas fa-exclamation-circle"></i> <?php echo esc($error); ?></div>
        <?php endif; ?>
        <form method="POST" autocomplete="on">
            <?php echo csrf_field(); ?>
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Enter username" required autofocus autocomplete="username">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter password" required autocomplete="current-password">
            </div>
            <button type="submit" class="btn-login">Sign In</button>
        </form>
    </div>
</body>
</html>
