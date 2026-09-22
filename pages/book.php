<?php
require_once __DIR__ . '/../includes/functions.php';
$pageTitle = 'Book a Tour | Bamba Adventures';
$pageDescription = 'Book your next adventure with Bamba Adventures. Luxury tours, safaris, and expeditions across all seven continents.';

$tourSlug = $_GET['tour'] ?? '';
$serviceSlug = $_GET['service'] ?? '';
$preSelectedTour = $tourSlug ? getTourBySlug($tourSlug) : null;
$allTours = getTours(['status' => 'active']);
$allServices = getServices();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $data = [
        'name' => trim($_POST['name'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'phone' => trim($_POST['phone'] ?? ''),
        'tour_id' => $_POST['tour_id'] ?: null,
        'travel_date' => $_POST['travel_date'] ?: null,
        'adults' => intval($_POST['adults'] ?? 1),
        'children' => intval($_POST['children'] ?? 0),
        'message' => trim($_POST['message'] ?? ''),
    ];
    
    if (empty($data['name']) || empty($data['email'])) {
        $error = 'Name and email are required.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO bookings (name, email, phone, tour_id, travel_date, adults, children, message) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$data['name'], $data['email'], $data['phone'], $data['tour_id'], $data['travel_date'], $data['adults'], $data['children'], $data['message']]);
        $success = 'Thank you! Your booking request has been received. Our team will contact you shortly.';
    }
}

include __DIR__ . '/../includes/header.php';
?>

<style>
    .page-wrapper { padding-top: 75px; }
    .booking-hero { background: linear-gradient(135deg, var(--primary-dark), var(--primary)); color: var(--white); text-align: center; padding: 9rem 5% 6rem; }
    .booking-hero h1 { font-size: clamp(2rem, 5vw, 3.5rem); margin-bottom: 0.75rem; }
    .booking-hero p { font-size: 1.1rem; opacity: 0.88; max-width: 520px; margin: 0 auto; }
    .form-wrap { max-width: 800px; margin: -4rem auto 0; padding: 0 5% 6rem; position: relative; z-index: 10; }
    .form-card { background: var(--white); border-radius: 20px; box-shadow: 0 10px 50px rgba(0,0,0,0.14); padding: 2.5rem; }
    .form-card h2 { font-size: 1.6rem; color: var(--primary-dark); margin-bottom: 0.4rem; }
    .form-card .sub { color: var(--text-light); font-size: 0.9rem; margin-bottom: 2rem; }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem; }
    .form-group { margin-bottom: 1rem; }
    .form-group label { display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.35rem; }
    .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 0.65rem 0.9rem; border: 1.5px solid #ddd; border-radius: 8px; font-family: inherit; font-size: 0.9rem; transition: border-color 0.3s; outline: none; }
    .form-group input:focus, .form-group select:focus, .form-group textarea:focus { border-color: var(--primary); }
    .form-group textarea { resize: vertical; min-height: 100px; }
    .submit-btn { width: 100%; background: var(--primary-dark); color: var(--white); border: none; padding: 1rem; border-radius: 30px; font-size: 1rem; font-weight: 700; cursor: pointer; font-family: inherit; transition: all 0.3s; margin-top: 0.5rem; }
    .submit-btn:hover { background: var(--primary); transform: translateY(-2px); }
    .alert { padding: 1rem; border-radius: 10px; margin-bottom: 1.5rem; }
    .alert-success { background: #d1fae5; color: #065f46; }
    .alert-error { background: #fee2e2; color: #991b1b; }
    @media(max-width:640px){ .form-row{grid-template-columns:1fr;} }
</style>

<div class="page-wrapper">
    <div class="booking-hero">
        <h1>Book Your Adventure</h1>
        <p>Fill in your details and our travel experts will craft your perfect journey.</p>
    </div>
    
    <div class="form-wrap">
        <div class="form-card">
            <h2>Tour Booking</h2>
            <p class="sub">Tell us about your dream trip</p>
            
            <?php if ($success): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $success; ?></div><?php endif; ?>
            <?php if ($error): ?><div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div><?php endif; ?>
            
            <form method="POST">
            <?php echo csrf_field(); ?>
                <div class="form-row">
                    <div class="form-group">
                        <label>Full Name *</label>
                        <input type="text" name="name" required placeholder="John Doe">
                    </div>
                    <div class="form-group">
                        <label>Email *</label>
                        <input type="email" name="email" required placeholder="john@example.com">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Phone</label>
                        <input type="tel" name="phone" placeholder="+254 700 000 000">
                    </div>
                    <div class="form-group">
                        <label>Tour Interested In</label>
                        <select name="tour_id">
                            <option value="">-- Select a tour --</option>
                            <?php foreach ($allTours as $t): ?>
                            <option value="<?php echo $t['id']; ?>" <?php echo ($preSelectedTour && $preSelectedTour['id'] == $t['id']) ? 'selected' : ''; ?>><?php echo esc($t['title']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Preferred Travel Date</label>
                        <input type="date" name="travel_date">
                    </div>
                    <div class="form-group">
                        <label>Adults</label>
                        <input type="number" name="adults" value="1" min="1">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Children</label>
                        <input type="number" name="children" value="0" min="0">
                    </div>
                </div>
                <div class="form-group">
                    <label>Additional Requests / Message</label>
                    <textarea name="message" placeholder="Tell us about any special requirements, dietary needs, or questions..."></textarea>
                </div>
                <button type="submit" class="submit-btn">Submit Booking Request</button>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
