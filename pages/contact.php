<?php
require_once __DIR__ . '/../includes/functions.php';
$pageTitle = 'Contact Us | Bamba Adventures';
$pageDescription = 'Get in touch with the Bamba Adventures team. We are here to help plan your perfect trip.';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    if (empty($name) || empty($email) || empty($message)) {
        $error = 'Name, email and message are required.';
    } elseif (!rate_limit($pdo, 'contact:' . client_ip(), 5, 10)) {
        $error = 'You have sent several messages already. Please wait a few minutes, or email us directly.';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO inquiries (name, email, phone, subject, message, page_url) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $email, $phone, $subject, $message, '/contact']);
            $success = 'Thank you! We have received your message and will respond shortly.';
        } catch (Exception $e) {
            // A DB problem must show the visitor a message, not a PHP fatal.
            $error = 'We could not send your message just now. Please try again, or email us directly.';
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>

<style>
.contact-layout { display: grid; grid-template-columns: 1fr 1.2fr; gap: 3rem; max-width: 1100px; margin: 0 auto; padding: 2rem 5% 6rem; }
.contact-info h2 { font-family: var(--font-serif); color: var(--primary-dark); margin-bottom: 1rem; }
.contact-info p { color: var(--text-light); line-height: 1.7; margin-bottom: 1.5rem; }
.contact-detail { display: flex; align-items: flex-start; gap: 0.8rem; margin-bottom: 1rem; }
.contact-detail i { color: var(--primary); font-size: 1.1rem; margin-top: 0.2rem; }
.contact-detail strong { display: block; font-size: 0.85rem; }
.contact-detail span { font-size: 0.85rem; color: var(--text-light); }
.contact-form { background: var(--white); border-radius: var(--radius); box-shadow: var(--shadow); padding: 2rem; }
.contact-form h2 { font-family: var(--font-serif); color: var(--primary-dark); margin-bottom: 0.5rem; }
.contact-form .sub { color: var(--text-light); font-size: 0.85rem; margin-bottom: 1.5rem; }
@media(max-width:768px){ .contact-layout{grid-template-columns:1fr;} }
</style>

<div class="page-hero">
    <h1>Contact Us</h1>
    <p>Have a question or ready to book? We would love to hear from you.</p>
</div>

<section class="section" style="padding-top:2rem;">
    <div class="contact-layout">
        <div class="contact-info">
            <h2>Get In Touch</h2>
            <p>Whether you are planning your next big adventure or just have a question, our team is here to help.</p>
            <div class="contact-detail">
                <i class="fas fa-map-marker-alt"></i>
                <div><strong>Address</strong><span><?php echo esc(getSetting('address') ?: 'Nairobi, Kenya'); ?></span></div>
            </div>
            <div class="contact-detail">
                <i class="fas fa-envelope"></i>
                <div><strong>Email</strong><span><?php echo esc(getSetting('email') ?: 'info@bambaadventures.com'); ?></span></div>
            </div>
            <div class="contact-detail">
                <i class="fas fa-phone"></i>
                <div><strong>Phone</strong><span><?php echo esc(getSetting('phone') ?: '+254 700 000 000'); ?></span></div>
            </div>
            <div class="contact-detail">
                <i class="fab fa-whatsapp"></i>
                <div><strong>WhatsApp</strong><span><?php echo esc(getSetting('whatsapp_number') ?: '+254 700 000 000'); ?></span></div>
            </div>
            <div style="margin-top:2rem;">
                <h3 style="font-size:1rem; margin-bottom:0.8rem;">Follow Us</h3>
                <div class="footer-social">
                    <?php if (getSetting('facebook')): ?><a href="<?php echo esc(getSetting('facebook')); ?>" target="_blank"><i class="fab fa-facebook-f"></i></a><?php endif; ?>
                    <?php if (getSetting('instagram')): ?><a href="<?php echo esc(getSetting('instagram')); ?>" target="_blank"><i class="fab fa-instagram"></i></a><?php endif; ?>
                    <?php if (getSetting('twitter')): ?><a href="<?php echo esc(getSetting('twitter')); ?>" target="_blank"><i class="fab fa-twitter"></i></a><?php endif; ?>
                    <?php if (getSetting('linkedin')): ?><a href="<?php echo esc(getSetting('linkedin')); ?>" target="_blank"><i class="fab fa-linkedin-in"></i></a><?php endif; ?>
                </div>
            </div>
        </div>
        <div class="contact-form">
            <h2>Send a Message</h2>
            <p class="sub">Fill out the form below and we will get back to you within 24 hours.</p>
            <?php if ($success): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $success; ?></div><?php endif; ?>
            <?php if ($error): ?><div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div><?php endif; ?>
            <form id="contactForm" method="POST">
            <?php echo csrf_field(); ?>
                <div class="form-row">
                    <div class="form-group"><label>Name *</label><input type="text" name="name" required></div>
                    <div class="form-group"><label>Email *</label><input type="email" name="email" required></div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label>Phone</label><input type="tel" name="phone"></div>
                    <div class="form-group"><label>Subject</label><input type="text" name="subject" placeholder="How can we help?"></div>
                </div>
                <div class="form-group"><label>Message *</label><textarea name="message" required placeholder="Tell us about your travel plans..."></textarea></div>
                <button type="submit" class="submit-btn">Send Message</button>
            </form>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
