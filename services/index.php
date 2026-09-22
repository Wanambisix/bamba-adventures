<?php
require_once __DIR__ . '/../includes/functions.php';
$slug = $_GET['slug'] ?? '';
if (!$slug) { header('Location: /index.php'); exit; }
$service = getServiceBySlug($slug);
if (!$service) { header('Location: /index.php'); exit; }
$pageTitle = $service['meta_title'] ?: $service['name'] . ' | Bamba Adventures';
$pageDescription = $service['meta_description'] ?: excerpt($service['description'], 160);
include __DIR__ . '/../includes/header.php';
?>

<style>
    .page-wrapper { padding-top: 75px; }
    .svc-hero { min-height: 300px; position: relative; display: flex; align-items: flex-end; background: linear-gradient(135deg, var(--primary-dark), var(--primary)); }
    .svc-hero-content { position: relative; z-index: 2; padding: 3rem 5%; max-width: 1400px; margin: 0 auto; width: 100%; color: #fff; }
    .svc-hero-content h1 { font-size: clamp(2rem, 4vw, 3rem); margin-bottom: 0.5rem; }
    .svc-hero-content p { font-size: 1.1rem; opacity: 0.9; max-width: 600px; }
    .svc-section { max-width: 1400px; margin: 0 auto; padding: 4rem 5%; }
    .svc-section p { color: var(--text-light); line-height: 1.8; font-size: 1.05rem; margin-bottom: 1rem; }
    .cta-box { background: var(--bg-light); border-radius: 20px; padding: 3rem; text-align: center; margin-top: 2rem; }
    .cta-box h3 { font-size: 1.5rem; margin-bottom: 1rem; }
    .btn-gold { background: var(--accent); color: var(--text-dark); padding: 0.9rem 2.5rem; border-radius: 30px; text-decoration: none; font-weight: 600; font-size: 1rem; transition: all 0.3s; display: inline-block; }
    .btn-gold:hover { transform: translateY(-3px); ; }
</style>

<div class="page-wrapper">
    <div class="svc-hero">
        <div class="svc-hero-content">
            <h1><?php echo esc($service['name']); ?></h1>
            <p><?php echo esc($service['description']); ?></p>
        </div>
    </div>
    
    <div class="svc-section">
        <?php if ($service['long_content']): ?>
            <?php echo nl2br(esc($service['long_content'])); ?>
        <?php else: ?>
            <p><?php echo esc($service['description']); ?></p>
            <p>Our team of travel experts is ready to help you plan the perfect experience. Whether you're looking for a luxury getaway or an adventurous expedition, we tailor every detail to your preferences.</p>
        <?php endif; ?>
        
        <div class="cta-box">
            <h3>Interested in <?php echo esc($service['name']); ?>?</h3>
            <p style="max-width:500px;margin:0 auto 1.5rem;">Get in touch with our team for a personalized quote and itinerary.</p>
            <a href="/book?service=<?php echo esc($service['slug']); ?>" class="btn-gold">Request a Quote</a>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
