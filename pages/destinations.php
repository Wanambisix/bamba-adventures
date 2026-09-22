<?php
require_once __DIR__ . '/../includes/functions.php';
$pageTitle = 'Destinations | Bamba Adventures';
$pageDescription = 'Explore our curated travel destinations across all seven continents.';

$destinations = getDestinations();
include __DIR__ . '/../includes/header.php';
?>

<div class="page-hero">
    <h1>Destinations</h1>
    <p>From the plains of Africa to the peaks of Antarctica, discover your next adventure</p>
</div>

<section class="section">
    <div class="card-grid card-grid-4">
        <?php if ($destinations): foreach ($destinations as $d): ?>
        <article class="card destination-card">
            <a href="/destination/<?php echo esc($d['slug']); ?>">
                <div class="card-image" style="background-image:url('<?php echo esc($d['image'] ?: '/assets/images/destination-placeholder.jpg'); ?>');"></div>
                <div class="card-body">
                    <h3><?php echo esc($d['name']); ?></h3>
                    <p><?php echo esc(truncate($d['short_description'] ?? '', 70)); ?></p>
                </div>
            </a>
        </article>
        <?php endforeach; else: ?>
        <div class="empty-state" style="grid-column:1/-1;"><i class="fas fa-globe"></i><p>No destinations yet.</p></div>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
