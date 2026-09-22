<?php
require_once __DIR__ . '/../includes/functions.php';
$pageTitle = 'Services | Bamba Adventures';
$pageDescription = 'Full-service travel solutions for discerning explorers.';

$services = getServices();
include __DIR__ . '/../includes/header.php';
?>

<div class="page-hero">
    <h1>Our Services</h1>
    <p>Everything you need for a seamless and unforgettable journey</p>
</div>

<section class="section">
    <div class="card-grid">
        <?php if ($services): foreach ($services as $s): ?>
        <article class="card">
            <a href="/service/<?php echo esc($s['slug']); ?>">
                <div class="card-image" style="background-image:url('<?php echo esc($s['image'] ?: '/assets/images/service-placeholder.jpg'); ?>'); height:220px;"></div>
                <div class="card-body">
                    <h3><?php echo esc($s['name']); ?></h3>
                    <p><?php echo esc(truncate($s['description'] ?? '', 100)); ?></p>
                </div>
            </a>
        </article>
        <?php endforeach; else: ?>
        <div class="empty-state" style="grid-column:1/-1;"><i class="fas fa-concierge-bell"></i><p>No services listed yet.</p></div>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
