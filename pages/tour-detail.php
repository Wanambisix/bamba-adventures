<?php
require_once __DIR__ . '/../includes/functions.php';

$slug = $_GET['slug'] ?? '';
$tour = getTourBySlug($slug);

if (!$tour) {
    http_response_code(404);
    $pageTitle = 'Tour Not Found';
    include __DIR__ . '/../includes/header.php';
    echo '<div style="padding:8rem 5%;text-align:center;"><h1>Tour Not Found</h1><p>The tour you are looking for does not exist.</p><a href="/" style="color:var(--primary-dark);">Back to Home</a></div>';
    include __DIR__ . '/../includes/footer.php';
    exit;
}

// ── AJAX booking handler ──────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'book_tour') {
    csrf_verify();
    rate_limit($pdo, 'book_tour:' . client_ip(), 5, 10);
    header('Content-Type: application/json');
    $name  = trim($_POST['name']  ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $travel_date = $_POST['travel_date'] ?: null;
    $adults   = intval($_POST['adults']   ?? 1);
    $children = intval($_POST['children'] ?? 0);
    $budget   = trim($_POST['budget']  ?? '');
    $message  = trim($_POST['message'] ?? '');
    $tour_id  = intval($_POST['tour_id'] ?? 0) ?: null;

    if (!$name || !$email) {
        echo json_encode(['success' => false, 'error' => 'Name and email are required.']);
        exit;
    }
    try {
        $full_message = $budget ? "Budget: $budget\n\n$message" : $message;
        $stmt = $pdo->prepare("INSERT INTO bookings (name, email, phone, tour_id, travel_date, adults, children, message, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'new', NOW())");
        $stmt->execute([$name, $email, $phone, $tour_id, $travel_date, $adults, $children, $full_message]);
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'Could not save booking. Please try again.']);
    }
    exit;
}

$pageTitle = esc($tour['title']) . ' | Bamba Adventures';
$pageDescription = esc(truncate(strip_tags($tour['description'] ?? ''), 150));

$gallery = [];
if (!empty($tour['images'])) {
    $gallery = json_decode($tour['images'], true) ?: [];
}

$relatedTours = getTours(['status' => 'active', 'destination_id' => $tour['destination_id']]);
$relatedTours = array_filter($relatedTours, fn($t) => $t['id'] != $tour['id']);
$relatedTours = array_slice($relatedTours, 0, 3);

include __DIR__ . '/../includes/header.php';
?>

<style>
/* ── Tour detail layout ─────────────────────────── */
.tour-wrap { max-width: 1400px; margin: 0 auto; padding: 5rem 5% 4rem; display: grid; grid-template-columns: 1fr 380px; gap: 3.5rem; align-items: start; }
.tour-main {}
.tour-sidebar { position: sticky; top: 95px; }

/* ── Gallery ────────────────────────────────────── */
.main-img { width: 100%; height: 460px; object-fit: cover; border-radius: 20px; display: block; }
.gallery-thumbs { display: flex; gap: 0.5rem; margin-top: 0.6rem; overflow-x: auto; padding-bottom: 4px; }
.gallery-thumbs img { width: 78px; height: 56px; object-fit: cover; border-radius: 8px; cursor: pointer; flex-shrink: 0; border: 2.5px solid transparent; transition: border 0.2s, opacity 0.2s; opacity: 0.7; }
.gallery-thumbs img.active, .gallery-thumbs img:hover { border-color: var(--primary-dark); opacity: 1; }

/* ── Header info ────────────────────────────────── */
.tour-location { font-size: 0.82rem; color: var(--primary); font-weight: 700; text-transform: uppercase; letter-spacing: 2px; margin: 2rem 0 0.5rem; display: flex; align-items: center; gap: 0.4rem; }
.tour-title { font-size: clamp(1.8rem, 3vw, 2.6rem); color: var(--text-dark); margin-bottom: 1.2rem; line-height: 1.2; }
.tour-meta-row { display: flex; gap: 1.5rem; flex-wrap: wrap; margin-bottom: 1.5rem; }
.tour-meta-pill { display: flex; align-items: center; gap: 0.4rem; font-size: 0.88rem; color: var(--text-light); background: var(--bg-light); padding: 0.4rem 0.9rem; border-radius: 20px; }
.tour-meta-pill i { color: var(--primary); }

/* ── Tabs ───────────────────────────────────────── */
.tour-tabs { display: flex; gap: 0.5rem; margin: 2.5rem 0 0; overflow-x: auto; padding: 0; flex-wrap: nowrap; }
.tour-tabs button {
    background: #f5f5f5;
    border: 2px solid transparent;
    padding: 0.7rem 1.4rem;
    font-size: 0.88rem;
    font-weight: 600;
    color: var(--text-light);
    cursor: pointer;
    white-space: nowrap;
    border-radius: 10px 10px 0 0;
    transition: all 0.25s;
    font-family: inherit;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.tour-tabs button i { font-size: 0.85rem; }
.tour-tabs button:hover { background: #fff; color: var(--primary-dark); border-color: #e0e0e0 #e0e0e0 transparent; }
.tour-tabs button.active {
    background: var(--white);
    color: var(--primary-dark);
    border-color: var(--primary-dark) var(--primary-dark) transparent;
    box-shadow: 0 -3px 0 var(--primary-dark) inset;
    position: relative;
    z-index: 1;
}
.tabs-body {
    border: 2px solid #e0e0e0;
    border-radius: 0 10px 10px 10px;
    background: var(--white);
    padding: 2rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 4px 16px rgba(0,0,0,0.05);
}
.tab-panel { display: none; }
.tab-panel.active { display: block; animation: fadeIn 0.3s; }
@keyframes fadeIn { from { opacity:0; transform:translateY(8px); } to { opacity:1; transform:translateY(0); } }
/* Rendered HTML content styles */
.tab-panel h2, .tab-panel h3, .tab-panel h4 { color: var(--primary-dark); margin: 1.2rem 0 0.5rem; font-family: 'Poppins', sans-serif; }
.tab-panel h2 { font-size: 1.4rem; }
.tab-panel h3 { font-size: 1.2rem; }
.tab-panel h4 { font-size: 1rem; }
.tab-panel p { color: var(--text-light); line-height: 1.85; font-size: 0.95rem; margin-bottom: 0.8rem; }
.tab-panel li { color: var(--text-light); line-height: 1.8; font-size: 0.95rem; margin-bottom: 0.4rem; }
.tab-panel ul { padding-left: 1.4rem; margin: 0.5rem 0 1rem; list-style: disc; }
.tab-panel ol { padding-left: 1.4rem; margin: 0.5rem 0 1rem; }
.tab-panel strong { color: var(--text-dark); }
.tour-desc { color: var(--text-light); line-height: 1.9; font-size: 1rem; }
.tour-desc p { margin-bottom: 0.8rem; }
.tour-desc h2, .tour-desc h3 { color: var(--text-dark); margin: 1.2rem 0 0.5rem; }

/* ── Sidebar card ───────────────────────────────── */
.sidebar-card { background: var(--white); border-radius: 20px; box-shadow: 0 8px 40px rgba(0,0,0,0.1); overflow: hidden; }
.sidebar-price-bar { background: linear-gradient(135deg, var(--primary-dark), var(--primary)); color: #fff; padding: 1.5rem; text-align: center; }
.sidebar-price-bar .from { font-size: 0.78rem; opacity: 0.8; letter-spacing: 1px; text-transform: uppercase; margin-bottom: 0.2rem; }
.sidebar-price-bar .amount { font-size: 2.2rem; font-weight: 700; font-family: 'Poppins', sans-serif; }
.sidebar-price-bar .per { font-size: 0.82rem; opacity: 0.8; margin-top: 0.1rem; }
.sidebar-body { padding: 1.5rem; }
.sidebar-body .book-btn { display: block; width: 100%; background: var(--accent); color: var(--text-dark); border: none; padding: 0.9rem; border-radius: 30px; font-size: 1rem; font-weight: 700; cursor: pointer; font-family: inherit; text-align: center; text-decoration: none; transition: all 0.3s; }
.sidebar-body .book-btn:hover { transform: translateY(-2px); }
.sidebar-body .enquire-btn { display: block; width: 100%; background: none; border: 2px solid var(--primary-dark); color: var(--primary-dark); padding: 0.75rem; border-radius: 30px; font-size: 0.9rem; font-weight: 600; cursor: pointer; font-family: inherit; text-align: center; margin-top: 0.75rem; transition: all 0.3s; }
.sidebar-body .enquire-btn:hover { background: var(--primary-dark); color: #fff; }
.sidebar-divider { border: none; border-top: 1px solid #eee; margin: 1.25rem 0; }
.sidebar-feature { display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem; font-size: 0.88rem; color: var(--text-light); }
.sidebar-feature i { width: 32px; height: 32px; background: var(--bg-light); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--primary); font-size: 0.8rem; flex-shrink: 0; }
.sidebar-wa { display: flex; align-items: center; justify-content: center; gap: 0.5rem; background: #25D366; color: #fff; border-radius: 30px; padding: 0.7rem; font-size: 0.88rem; font-weight: 600; text-decoration: none; margin-top: 0.75rem; transition: all 0.3s; }
.sidebar-wa:hover { background: #1ebe5d; transform: translateY(-1px); }

/* ── Related tours ──────────────────────────────── */
.related-section { background: var(--bg-light); padding: 5rem 5%; }
.related-inner { max-width: 1400px; margin: 0 auto; }
.related-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 2rem; margin-top: 2.5rem; }
.related-card { background: var(--white); border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.07); transition: all 0.3s; text-decoration: none; color: inherit; display: block; }
.related-card:hover { transform: translateY(-6px); box-shadow: 0 16px 40px rgba(0,0,0,0.12); }
.related-card img { width: 100%; height: 180px; object-fit: cover; display: block; }
.related-card-body { padding: 1.2rem; }
.related-card-body h3 { font-size: 1.05rem; color: var(--text-dark); margin-bottom: 0.4rem; }
.related-card-body p { font-size: 0.85rem; color: var(--text-light); }
.related-card-price { font-weight: 700; color: var(--primary-dark); font-size: 1rem; margin-top: 0.6rem; }

/* ── Booking Modal ──────────────────────────────── */
.modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.65); z-index: 3000; align-items: center; justify-content: center; padding: 1rem; }
.modal-overlay.active { display: flex; }
.modal-box { background: var(--white); border-radius: 20px; width: 100%; max-width: 540px; max-height: 92vh; overflow-y: auto; position: relative; box-shadow: 0 30px 80px rgba(0,0,0,0.3); }
.modal-header { background: linear-gradient(135deg, var(--primary-dark), var(--primary)); color: #fff; padding: 1.5rem 2rem 1.25rem; border-radius: 20px 20px 0 0; position: relative; }
.modal-header h3 { font-size: 1.3rem; margin-bottom: 0.25rem; }
.modal-header p { font-size: 0.85rem; opacity: 0.85; }
.modal-close { position: absolute; top: 1rem; right: 1rem; background: rgba(255,255,255,0.2); border: none; width: 32px; height: 32px; border-radius: 50%; cursor: pointer; color: #fff; font-size: 1rem; display: flex; align-items: center; justify-content: center; transition: background 0.2s; }
.modal-close:hover { background: rgba(255,255,255,0.35); }
.modal-body { padding: 1.75rem 2rem 2rem; }
.mform-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.mform-group { margin-bottom: 1rem; }
.mform-group label { display: block; font-size: 0.82rem; font-weight: 600; margin-bottom: 0.35rem; color: var(--text-dark); }
.mform-group input, .mform-group select, .mform-group textarea { width: 100%; padding: 0.65rem 0.9rem; border: 1.5px solid #ddd; border-radius: 10px; font-family: inherit; font-size: 0.9rem; transition: border-color 0.2s; outline: none; background: #fafafa; color: var(--text-dark); }
.mform-group input:focus, .mform-group select:focus, .mform-group textarea:focus { border-color: var(--primary); background: #fff; }
.mform-group textarea { resize: vertical; min-height: 80px; }
.modal-submit { width: 100%; background: var(--primary-dark); color: #fff; border: none; padding: 0.95rem; border-radius: 30px; font-size: 1rem; font-weight: 700; cursor: pointer; font-family: inherit; transition: all 0.3s; margin-top: 0.5rem; }
.modal-submit:hover { background: var(--primary); transform: translateY(-2px); }
.modal-submit:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }
.modal-success { text-align: center; padding: 2rem 1rem; }
.modal-success .check-icon { width: 64px; height: 64px; background: #d1fae5; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; }
.modal-success .check-icon i { font-size: 1.8rem; color: #059669; }
.modal-success h4 { font-size: 1.3rem; color: var(--text-dark); margin-bottom: 0.5rem; }
.modal-success p { color: var(--text-light); font-size: 0.95rem; }

/* ── Breadcrumb ─────────────────────────────────── */
.breadcrumb-bar { background: var(--bg-light); padding: 0.9rem 5%; border-bottom: 1px solid #eee; margin-top: 75px; }
.breadcrumb-bar ol { max-width: 1400px; margin: 0 auto; list-style: none; display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; color: var(--text-light); flex-wrap: wrap; }
.breadcrumb-bar ol li a { color: var(--primary); text-decoration: none; }
.breadcrumb-bar ol li a:hover { text-decoration: underline; }
.breadcrumb-bar ol li + li::before { content: '›'; margin-right: 0.5rem; color: #ccc; }

@media(max-width: 1100px) { .tour-wrap { grid-template-columns: 1fr; } .tour-sidebar { position: static; } .main-img { height: 340px; } }
@media(max-width: 768px) { .related-grid { grid-template-columns: 1fr; } .mform-row { grid-template-columns: 1fr; } .modal-body { padding: 1.25rem; } }
@media(max-width: 640px) { .related-grid { grid-template-columns: 1fr; } .main-img { height: 250px; } }
</style>

<!-- Breadcrumb -->
<div class="breadcrumb-bar">
    <ol>
        <li><a href="/">Home</a></li>
        <li><a href="/tours">Tours</a></li>
        <?php if (!empty($tour['destination_name'])): ?>
        <li><a href="/destination/<?= esc($tour['destination_slug'] ?? '') ?>"><?= esc($tour['destination_name']) ?></a></li>
        <?php endif; ?>
        <li><?= esc($tour['title']) ?></li>
    </ol>
</div>

<!-- Tour Content -->
<div class="tour-wrap">

    <!-- LEFT: Gallery + Details + Tabs -->
    <div class="tour-main">

        <!-- Gallery -->
        <?php $heroImg = $gallery[0] ?? 'https://images.unsplash.com/photo-1476514525535-07fb3b4ae5f1?w=1200&q=80'; ?>
        <img src="<?= esc($heroImg) ?>" alt="<?= esc($tour['title']) ?>" class="main-img" id="mainImage">
        <?php if (count($gallery) > 1): ?>
        <div class="gallery-thumbs">
            <?php foreach ($gallery as $i => $img): ?>
            <img src="<?= esc($img) ?>" alt="Gallery <?= $i+1 ?>" class="<?= $i === 0 ? 'active' : '' ?>"
                 onclick="document.getElementById('mainImage').src=this.src; document.querySelectorAll('.gallery-thumbs img').forEach(t=>t.classList.remove('active')); this.classList.add('active');">
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Tour heading -->
        <p class="tour-location"><i class="fas fa-map-marker-alt"></i> <?= esc($tour['country_name'] ?: ($tour['destination_name'] ?? 'Multi-Destination')) ?></p>
        <h1 class="tour-title"><?= esc($tour['title']) ?></h1>

        <div class="tour-meta-row">
            <?php if (!empty($tour['duration'])): ?>
            <span class="tour-meta-pill"><i class="far fa-clock"></i> <?= esc($tour['duration']) ?></span>
            <?php endif; ?>
            <?php if (!empty($tour['max_group_size'])): ?>
            <span class="tour-meta-pill"><i class="far fa-users"></i> Max <?= esc($tour['max_group_size']) ?> people</span>
            <?php endif; ?>
            <span class="tour-meta-pill"><i class="fas fa-star"></i> Expert Guides</span>
            <span class="tour-meta-pill"><i class="fas fa-shield-alt"></i> Fully Insured</span>
        </div>

        <div class="tour-desc"><?= $tour['description'] ? $tour['description'] : '<p>Description coming soon.</p>' ?></div>

        <!-- Tabs -->
        <div class="tour-tabs">
            <button class="active" data-tab="highlights"><i class="fas fa-star"></i> Highlights</button>
            <button data-tab="itinerary"><i class="fas fa-map-signs"></i> Itinerary</button>
            <button data-tab="inclusions"><i class="fas fa-check-circle"></i> Inclusions</button>
            <button data-tab="exclusions"><i class="fas fa-times-circle"></i> Exclusions</button>
            <button data-tab="service_notes"><i class="fas fa-info-circle"></i> Service Notes</button>
            <button data-tab="optional_addons"><i class="fas fa-plus-circle"></i> Optional Add-ons</button>
        </div>

        <div class="tabs-body">
            <div id="highlights" class="tab-panel active">
                <?= $tour['highlights'] ? $tour['highlights'] : '<p>No highlights provided yet.</p>' ?>
            </div>
            <div id="itinerary" class="tab-panel">
                <?= $tour['itinerary'] ? $tour['itinerary'] : '<p>Itinerary coming soon.</p>' ?>
            </div>
            <div id="inclusions" class="tab-panel">
                <?= $tour['inclusions'] ? $tour['inclusions'] : '<p>Details coming soon.</p>' ?>
            </div>
            <div id="exclusions" class="tab-panel">
                <?= $tour['exclusions'] ? $tour['exclusions'] : '<p>Details coming soon.</p>' ?>
            </div>
            <div id="service_notes" class="tab-panel">
                <?= $tour['service_notes'] ? $tour['service_notes'] : '<p>No service notes available.</p>' ?>
            </div>
            <div id="optional_addons" class="tab-panel">
                <?= $tour['optional_addons'] ? $tour['optional_addons'] : '<p>No optional add-ons available.</p>' ?>
            </div>
        </div>

        <!-- Cancellation policy link -->
        <div style="margin-top:1.5rem;padding-top:1.25rem;border-top:1px solid #eee;">
            <a href="/cancellation-policy" style="display:inline-flex;align-items:center;gap:0.5rem;color:var(--text-light);font-size:0.88rem;text-decoration:none;transition:color 0.2s;" onmouseover="this.style.color='var(--primary-dark)'" onmouseout="this.style.color='var(--text-light)'">
                <i class="fas fa-file-contract" style="color:var(--primary);"></i>
                Read our Cancellation Policy
                <i class="fas fa-arrow-right" style="font-size:0.75rem;"></i>
            </a>
        </div>

    </div><!-- /tour-main -->

    <!-- RIGHT: Sidebar booking card -->
    <div class="tour-sidebar">
        <div class="sidebar-card">
            <div class="sidebar-price-bar">
                <div class="from">Starting from</div>
                <div class="amount"><?= $tour['price'] ? '$' . number_format($tour['price']) : 'POA' ?></div>
                <?php if (!empty($tour['price_note'])): ?>
                <div class="per"><?= esc($tour['price_note']) ?></div>
                <?php else: ?>
                <div class="per">per person</div>
                <?php endif; ?>
            </div>
            <div class="sidebar-body">
                <button class="book-btn" onclick="openBookingModal()">
                    <i class="fas fa-calendar-check"></i> &nbsp;Book This Tour
                </button>
                <button class="enquire-btn" onclick="openBookingModal()">
                    <i class="fas fa-envelope"></i> &nbsp;Send Enquiry
                </button>

                <hr class="sidebar-divider">

                <?php if (!empty($tour['duration'])): ?>
                <div class="sidebar-feature"><i class="far fa-clock"></i> <?= esc($tour['duration']) ?></div>
                <?php endif; ?>
                <?php if (!empty($tour['max_group_size'])): ?>
                <div class="sidebar-feature"><i class="fas fa-users"></i> Max group: <?= esc($tour['max_group_size']) ?></div>
                <?php endif; ?>
                <div class="sidebar-feature"><i class="fas fa-star"></i> Expert-led guided tours</div>
                <div class="sidebar-feature"><i class="fas fa-shield-alt"></i> Fully insured & licensed</div>
                <div class="sidebar-feature"><i class="fas fa-undo"></i> Flexible cancellation</div>

                <a href="https://wa.me/254706606606?text=Hi!%20I'm%20interested%20in%20the%20<?= urlencode($tour['title']) ?>%20package." target="_blank" class="sidebar-wa">
                    <i class="fab fa-whatsapp"></i> Chat on WhatsApp
                </a>
            </div>
        </div>
    </div><!-- /tour-sidebar -->

</div><!-- /tour-wrap -->

<!-- Related Tours -->
<?php if (!empty($relatedTours)): ?>
<div class="related-section">
    <div class="related-inner">
        <h2 style="font-family:'Poppins',sans-serif;font-size:clamp(1.6rem,3vw,2.2rem);color:var(--text-dark);margin-bottom:0.4rem;">You May Also Like</h2>
        <p style="color:var(--text-light);font-size:0.95rem;">More tours from <?= esc($tour['destination_name'] ?? 'our collection') ?></p>
        <div class="related-grid">
            <?php foreach ($relatedTours as $t):
                $rImgs = json_decode($t['images'] ?? '[]', true);
                $rImg  = $rImgs[0] ?? $t['image'] ?? 'https://images.unsplash.com/photo-1476514525535-07fb3b4ae5f1?w=600&q=80';
            ?>
            <a href="/tour/<?= esc($t['slug']) ?>" class="related-card">
                <img src="<?= esc($rImg) ?>" alt="<?= esc($t['title']) ?>">
                <div class="related-card-body">
                    <h3><?= esc($t['title']) ?></h3>
                    <p><?= esc(truncate($t['description'] ?? '', 75)) ?></p>
                    <div class="related-card-price"><?= $t['price'] ? 'From $' . number_format($t['price']) : 'Contact for price' ?></div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ══ BOOKING MODAL ══════════════════════════════════════════════ -->
<div id="bookingModal" class="modal-overlay" onclick="if(event.target===this)closeBookingModal()">
    <div class="modal-box">
        <div class="modal-header">
            <button class="modal-close" onclick="closeBookingModal()" aria-label="Close">&times;</button>
            <h3>Book: <?= esc($tour['title']) ?></h3>
            <p>Fill in your details and we'll confirm within 24 hours.</p>
        </div>
        <div class="modal-body" id="modalBody">
            <form id="bookingForm" onsubmit="submitBooking(event)">
                <input type="hidden" name="action" value="book_tour">
                <input type="hidden" name="tour_id" value="<?= $tour['id'] ?>">

                <div class="mform-group">
                    <label>Full Name *</label>
                    <input type="text" name="name" placeholder="Your full name" required>
                </div>

                <div class="mform-row">
                    <div class="mform-group">
                        <label>Email *</label>
                        <input type="email" name="email" placeholder="you@email.com" required>
                    </div>
                    <div class="mform-group">
                        <label>Phone / WhatsApp</label>
                        <input type="tel" name="phone" placeholder="+254 700 000 000">
                    </div>
                </div>

                <div class="mform-row">
                    <div class="mform-group">
                        <label>Preferred Travel Date</label>
                        <input type="date" name="travel_date" min="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="mform-group">
                        <label>No. of Adults</label>
                        <input type="number" name="adults" min="1" value="1" placeholder="2">
                    </div>
                </div>

                <div class="mform-row">
                    <div class="mform-group">
                        <label>No. of Children</label>
                        <input type="number" name="children" min="0" value="0">
                    </div>
                    <div class="mform-group">
                        <label>Budget Range</label>
                        <select name="budget">
                            <option value="">Select range</option>
                            <option>Under $1,000</option>
                            <option>$1,000 – $2,000</option>
                            <option>$2,000 – $3,500</option>
                            <option>$3,500 – $5,000</option>
                            <option>$5,000 – $10,000</option>
                            <option>Above $10,000</option>
                        </select>
                    </div>
                </div>

                <div class="mform-group">
                    <label>Message / Special Requests</label>
                    <textarea name="message" placeholder="Tell us about any special requirements, dietary needs, or questions…"></textarea>
                </div>

                <button type="submit" class="modal-submit" id="submitBtn">
                    <i class="fas fa-paper-plane"></i> &nbsp;Send Booking Request
                </button>
            </form>
        </div>
    </div>
</div>

<script>
/* ── Tabs ─────────────────────────────── */
document.querySelectorAll('.tour-tabs button').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.tour-tabs button').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
        this.classList.add('active');
        document.getElementById(this.dataset.tab).classList.add('active');
    });
});

/* ── Booking modal ────────────────────── */
function openBookingModal() {
    document.getElementById('bookingModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}
function closeBookingModal() {
    document.getElementById('bookingModal').classList.remove('active');
    document.body.style.overflow = '';
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeBookingModal(); });

async function submitBooking(e) {
    e.preventDefault();
    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> &nbsp;Sending…';

    const form = document.getElementById('bookingForm');
    const data = new FormData(form);

    try {
        const res  = await fetch(window.location.href, { method: 'POST', body: data });
        const json = await res.json();

        if (json.success) {
            document.getElementById('modalBody').innerHTML = `
                <div class="modal-success">
                    <div class="check-icon"><i class="fas fa-check"></i></div>
                    <h4>Booking Request Sent!</h4>
                    <p>Thank you! Our team will review your request and get back to you within 24 hours.</p>
                    <p style="margin-top:0.75rem;">Need faster assistance? <a href="https://wa.me/254706606606" target="_blank" style="color:var(--primary-dark);font-weight:600;">Chat with us on WhatsApp</a>.</p>
                </div>`;
        } else {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-paper-plane"></i> &nbsp;Send Booking Request';
            alert(json.error || 'Something went wrong. Please try again.');
        }
    } catch (err) {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-paper-plane"></i> &nbsp;Send Booking Request';
        alert('Connection error. Please try again.');
    }
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
