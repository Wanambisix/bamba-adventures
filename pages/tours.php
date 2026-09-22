<?php
require_once __DIR__ . '/../includes/functions.php';
$pageTitle = 'All Tours | Bamba Adventures';
$pageDescription = 'Browse our complete collection of curated luxury tours and adventures worldwide.';

$filters = ['status' => 'active'];
if (!empty($_GET['destination'])) $filters['destination_id'] = intval($_GET['destination']);
if (!empty($_GET['featured'])) $filters['featured'] = true;

$tours = getTours($filters);
$destinations = getDestinations();

include __DIR__ . '/../includes/header.php';
?>

<style>
.filter-bar { display: flex; gap: 1rem; flex-wrap: wrap; justify-content: center; margin-bottom: 2.5rem; }
.filter-bar a { padding: 0.5rem 1.2rem; border-radius: 30px; font-size: 0.8rem; font-weight: 600; border: 1.5px solid var(--border); transition: var(--transition); }
.filter-bar a:hover, .filter-bar a.active { background: var(--primary); color: var(--white); border-color: var(--primary); }
</style>

<div class="page-hero">
    <h1>Our Tours</h1>
    <p>Curated adventures for every kind of explorer</p>
</div>

<section class="section">
    <div class="filter-bar">
        <a href="/tours/kenyan-tours.html" class="<?php echo empty($_GET) ? 'active' : ''; ?>">All Tours</a>
        <a href="/tours?featured=1" class="<?php echo !empty($_GET['featured']) ? 'active' : ''; ?>">Featured</a>
        <?php foreach ($destinations as $d): ?>
        <a href="/tours?destination=<?php echo $d['id']; ?>" class="<?php echo (!empty($_GET['destination']) && $_GET['destination'] == $d['id']) ? 'active' : ''; ?>"><?php echo esc($d['name']); ?></a>
        <?php endforeach; ?>
    </div>
    <div class="card-grid">
        <?php if ($tours): foreach ($tours as $t): ?>
        <article class="card">
            <a href="/tour/<?php echo esc($t['slug']); ?>">
                <div class="card-image" style="background-image:url('<?php echo esc(firstImage($t['images']) ?: '/assets/images/tour-placeholder.jpg'); ?>');">
                    <?php if ($t['featured']): ?><span class="card-badge">Featured</span><?php endif; ?>
                </div>
                <div class="card-body">
                    <p style="font-size:0.78rem; color:var(--primary); font-weight:600; text-transform:uppercase; margin-bottom:0.3rem;"><i class="fas fa-map-marker-alt"></i> <?php echo esc($t['destination_name'] ?? 'Multi-Destination'); ?></p>
                    <h3><?php echo esc($t['title']); ?></h3>
                    <p><?php echo esc(truncate($t['description'] ?? '', 90)); ?></p>
                    <div class="card-meta">
                        <span><i class="fas fa-clock"></i> <?php echo esc($t['duration'] ?? 'N/A'); ?></span>
                        <span><i class="fas fa-users"></i> <?php echo esc($t['max_group_size'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="card-price"><?php echo formatPrice($t['price'], $t['price_note'] ?? ''); ?></div>
                </div>
            </a>
        </article>
        <?php endforeach; else: ?>
        <div class="empty-state" style="grid-column:1/-1;"><i class="fas fa-hiking"></i><p>No tours found.</p></div>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
