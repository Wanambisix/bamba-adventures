<?php
require_once __DIR__ . '/../includes/functions.php';
$slug = $_GET['slug'] ?? '';
$service = getServiceBySlug($slug);

if (!$service) {
    http_response_code(404);
    $pageTitle = 'Service Not Found';
    include __DIR__ . '/../includes/header.php';
    echo '<div class="page-hero"><h1>Service Not Found</h1><p>The service you are looking for does not exist.</p></div>';
    include __DIR__ . '/../includes/footer.php';
    exit;
}

$pageTitle = esc($service['name']) . ' | Bamba Adventures';
include __DIR__ . '/../includes/header.php';
?>

<style>
.service-hero {
    position: relative; height: 400px; background-size: cover; background-position: center;
    display: flex; align-items: center; justify-content: center;
}
.service-hero-overlay { position: absolute; inset: 0; background: rgba(0,0,0,0.5); }
.service-hero-content { position: relative; z-index: 2; color: var(--white); text-align: center; padding: 0 5%; }
.service-hero-content h1 { font-family: var(--font-serif); font-size: clamp(2rem, 5vw, 3rem); margin-bottom: 0.5rem; }
.service-body { max-width: 800px; margin: 0 auto; padding: 4rem 5%; color: var(--text-light); line-height: 1.8; }
.service-body h2 { font-family: var(--font-serif); color: var(--primary-dark); margin: 2rem 0 1rem; }
</style>

<div class="service-hero" style="background-image:url('<?php echo esc($service['image'] ?: '/assets/images/service-placeholder.jpg'); ?>');">
    <div class="service-hero-overlay"></div>
    <div class="service-hero-content">
        <h1><?php echo esc($service['name']); ?></h1>
        <p><?php echo esc(truncate($service['description'] ?? '', 120)); ?></p>
    </div>
</div>

<div class="breadcrumb" style="padding-top:1rem;">
    <div class="container">
        <ol>
            <li><a href="/">Home</a></li>
            <li><a href="/services/air-ticketing.html">Services</a></li>
            <li><?php echo esc($service['name']); ?></li>
        </ol>
    </div>
</div>

<div class="service-body">
    <?php echo nl2br(esc($service['description'] ?? '')); ?>
    <div class="text-center mt-2">
        <a href="/book" class="btn btn-primary">Get In Touch</a>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
