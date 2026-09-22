<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$settingsList = [
    'site_name' => 'Site Name',
    'site_tagline' => 'Tagline',
    'contact_email' => 'Contact Email',
    'contact_phone' => 'Contact Phone',
    'contact_address' => 'Contact Address',
    'contact_whatsapp' => 'WhatsApp Number (with country code)',
    'contact_hours' => 'Working Hours',
    'social_facebook' => 'Facebook URL',
    'social_instagram' => 'Instagram URL',
    'social_twitter' => 'Twitter/X URL',
    'social_linkedin' => 'LinkedIn URL',
    'social_youtube' => 'YouTube URL',
    'social_tiktok' => 'TikTok URL',
    'hero_title' => 'Homepage Hero Title',
    'hero_subtitle' => 'Homepage Hero Subtitle',
    'hero_search_placeholder' => 'Search Placeholder Text',
    'hero_image' => 'Hero Background Image URL',
    'cta_title' => 'CTA Strip Title',
    'cta_subtitle' => 'CTA Strip Subtitle',
    'cta_button_text' => 'CTA Button Text',
    'cta_button_link' => 'CTA Button Link',
    'footer_copyright' => 'Footer Copyright Text',
    'footer_about_text' => 'Footer About Text',
    'site_url' => 'Site URL (e.g. https://bambaadventures.co.ke)',
    'ga_id' => 'Google Analytics ID',
    'meta_title_default' => 'Default Meta Title',
    'meta_description_default' => 'Default Meta Description',
    'announcement_bar' => 'Announcement Bar (HTML allowed, leave empty to hide)',
];

$passwordMessage = '';
$passwordError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    if (isset($_POST['change_password'])) {
        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE id = ?");
        $stmt->execute([$_SESSION['admin_id']]);
        $admin = $stmt->fetch();
        
        if (!$admin || !password_verify($current, $admin['password_hash'])) {
            $passwordError = 'Current password is incorrect.';
        } elseif (strlen($new) < 6) {
            $passwordError = 'New password must be at least 6 characters.';
        } elseif ($new !== $confirm) {
            $passwordError = 'New passwords do not match.';
        } else {
            $pdo->prepare("UPDATE admins SET password_hash = ? WHERE id = ?")
                ->execute([password_hash($new, PASSWORD_DEFAULT), $_SESSION['admin_id']]);
            $passwordMessage = 'Password changed successfully!';
        }
    } else {
        foreach ($settingsList as $key => $label) {
            setSetting($key, $_POST[$key] ?? '');
        }
        // Handle logo upload
        if (!empty($_FILES['logo']['tmp_name'])) {
            $logoResult = uploadImage($_FILES['logo'], 'site');
            if (!empty($logoResult['success'])) {
                setSetting('site_logo', '/' . $logoResult['path']);
            }
        }
        $success = 'Settings saved successfully!';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Settings | Bamba Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main">
        <div class="topbar"><h1>Site Settings</h1></div>
        <div class="content">
            <?php if (!empty($success)): ?><div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>
            
            <!-- Password Change -->
            <div class="card">
                <div class="card-header"><h2><i class="fas fa-lock" style="color:var(--primary);"></i> Change Admin Password</h2></div>
                <div class="card-body">
                    <?php if ($passwordMessage): ?><div class="alert alert-success"><?php echo $passwordMessage; ?></div><?php endif; ?>
                    <?php if ($passwordError): ?><div class="alert alert-error"><?php echo $passwordError; ?></div><?php endif; ?>
                    <form method="POST">
            <?php echo csrf_field(); ?>
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Current Password</label>
                                <input type="password" name="current_password" required>
                            </div>
                            <div class="form-group">
                                <label>New Password</label>
                                <input type="password" name="new_password" required minlength="6">
                            </div>
                            <div class="form-group">
                                <label>Confirm New Password</label>
                                <input type="password" name="confirm_password" required minlength="6">
                            </div>
                        </div>
                        <button type="submit" name="change_password" class="btn btn-primary" style="margin-top:0.5rem;"><i class="fas fa-key"></i> Change Password</button>
                    </form>
                </div>
            </div>
            
            <form method="POST" enctype="multipart/form-data">
            <?php echo csrf_field(); ?>
                <!-- Logo Upload -->
                <div class="card">
                    <div class="card-header"><h2><i class="fas fa-image" style="color:var(--primary);"></i> Site Logo</h2></div>
                    <div class="card-body">
                        <div style="display:flex;align-items:center;gap:1.5rem;">
                            <img src="<?php echo esc($currentLogo); ?>" alt="Current Logo" style="height:60px;max-width:200px;object-fit:contain;border:1px solid var(--border);padding:4px;background:#faf9f7;">
                            <div>
                                <label style="display:block;font-size:0.85rem;font-weight:600;margin-bottom:0.4rem;">Upload New Logo</label>
                                <input type="file" name="logo" accept="image/*" style="font-size:0.85rem;">
                                <small style="color:var(--text-light);font-size:0.8rem;">Recommended: PNG with transparent background, max 500KB</small>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
                $sections = [
                    'General' => ['site_name','site_tagline','announcement_bar'],
                    'Contact Info' => ['contact_email','contact_phone','contact_address','contact_whatsapp','contact_hours'],
                    'Social Media' => ['social_facebook','social_instagram','social_twitter','social_linkedin','social_youtube','social_tiktok'],
                    'Homepage' => ['hero_title','hero_subtitle','hero_search_placeholder','hero_image'],
                    'CTA Strip' => ['cta_title','cta_subtitle','cta_button_text','cta_button_link'],
                    'Footer' => ['footer_copyright','footer_about_text'],
                    'SEO & Analytics' => ['site_url','meta_title_default','meta_description_default','ga_id'],
                ];
                $currentLogo = getSetting('site_logo', '/assets/images/bamba-logo.png');
                foreach ($sections as $section => $keys):
                ?>
                <div class="card">
                    <div class="card-header"><h2><?php echo $section; ?></h2></div>
                    <div class="card-body">
                        <div class="form-grid">
                            <?php foreach ($keys as $key): ?>
                            <div class="form-group <?php echo in_array($key, ['footer_about_text','meta_description_default','announcement_bar']) ? 'full' : ''; ?>">
                                <label><?php echo $settingsList[$key]; ?></label>
                                <?php if (in_array($key, ['footer_about_text','meta_description_default','announcement_bar'])): ?>
                                <textarea name="<?php echo $key; ?>" rows="3"><?php echo esc(getSetting($key)); ?></textarea>
                                <?php else: ?>
                                <input type="text" name="<?php echo $key; ?>" value="<?php echo esc(getSetting($key)); ?>">
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                <div style="margin-top:1rem;">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save All Settings</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
