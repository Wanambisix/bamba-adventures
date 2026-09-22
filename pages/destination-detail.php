<?php
require_once __DIR__ . '/../includes/functions.php';
$slug = $_GET['slug'] ?? '';
$destination = getDestinationBySlug($slug);

if (!$destination) {
    http_response_code(404);
    $pageTitle = 'Destination Not Found';
    include __DIR__ . '/../includes/header.php';
    echo '<div class="page-hero"><h1>Destination Not Found</h1><p>The destination you are looking for does not exist.</p></div>';
    include __DIR__ . '/../includes/footer.php';
    exit;
}

$pageTitle = esc($destination['name']) . ' | Bamba Adventures';
$toursInDestination = getTours(['status' => 'active', 'destination_id' => $destination['id']]);

include __DIR__ . '/../includes/header.php';
?>

<style>
.destination-hero {
    position: relative; height: 500px; background-size: cover; background-position: center;
    display: flex; align-items: flex-end;
}
.destination-hero-overlay { position: absolute; inset: 0; background: linear-gradient(transparent 30%, rgba(0,0,0,0.7)); }
.destination-hero-content { position: relative; z-index: 2; color: var(--white); padding: 4rem 5%; max-width: 1200px; margin: 0 auto; width: 100%; }
.destination-hero-content h1 { font-family: var(--font-serif); font-size: clamp(2rem, 5vw, 3.5rem); margin-bottom: 0.5rem; }
.destination-hero-content p { opacity: 0.85; max-width: 600px; }
.destination-content { max-width: 800px; margin: 0 auto; padding: 4rem 5%; color: var(--text-light); line-height: 1.8; }
.destination-content h2 { font-family: var(--font-serif); color: var(--primary-dark); margin: 2rem 0 1rem; }
</style>

<div class="destination-hero" style="background-image:url('<?php echo esc($destination['image'] ?: '/assets/images/destination-placeholder.jpg'); ?>');">
    <div class="destination-hero-overlay"></div>
    <div class="destination-hero-content">
        <h1><?php echo esc($destination['name']); ?></h1>
        <p><?php echo esc(truncate($destination['description'] ?? '', 120)); ?></p>
    </div>
</div>

<div class="breadcrumb" style="padding-top:1rem;">
    <div class="container">
        <ol>
            <li><a href="/">Home</a></li>
            <li><a href="/destinations/africa.html">Destinations</a></li>
            <li><?php echo esc($destination['name']); ?></li>
        </ol>
    </div>
</div>

<div class="destination-content">
    <?php echo nl2br(esc($destination['description'] ?? '')); ?>
</div>

<?php if ($toursInDestination): ?>
<section class="section section-light">
    <div class="container">
        <div class="section-header"><h2>Tours in <?php echo esc($destination['name']); ?></h2></div>
        <div class="card-grid">
            <?php foreach ($toursInDestination as $t): ?>
            <article class="card">
                <a href="/tour/<?php echo esc($t['slug']); ?>">
                    <div class="card-image" style="background-image:url('<?php echo esc(firstImage($t['images']) ?: '/assets/images/tour-placeholder.jpg'); ?>');"></div>
                    <div class="card-body">
                        <h3><?php echo esc($t['title']); ?></h3>
                        <p><?php echo esc(truncate($t['description'] ?? '', 80)); ?></p>
                        <div class="card-price">$<?php echo number_format($t['price'] ?? 0); ?></div>
                    </div>
                </a>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
