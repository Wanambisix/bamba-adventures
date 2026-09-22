<?php
require_once __DIR__ . '/../includes/functions.php';
$pageTitle = 'Terms & Conditions | Bamba Adventures';
$pageDescription = 'Terms and conditions for booking with Bamba Adventures.';

include __DIR__ . '/../includes/header.php';
?>

<style>
.terms-content { max-width: 800px; margin: 0 auto; padding: 4rem 5% 6rem; color: var(--text-light); line-height: 1.8; }
.terms-content h2 { font-family: var(--font-serif); color: var(--primary-dark); margin: 2.5rem 0 1rem; font-size: 1.3rem; }
.terms-content h3 { font-size: 1rem; color: var(--text); margin: 1.5rem 0 0.5rem; }
.terms-content p { margin-bottom: 1rem; }
.terms-content ul { padding-left: 1.2rem; margin-bottom: 1rem; }
.terms-content ul li { margin-bottom: 0.4rem; }
</style>

<div class="page-hero">
    <h1>Terms & Conditions</h1>
    <p>Please read these terms carefully before booking with us</p>
</div>

<div class="terms-content">
    <h2>1. Booking & Payment</h2>
    <p>A deposit of 30% of the total tour price is required to confirm your booking. The remaining balance is due 60 days before departure. For bookings made within 60 days of departure, full payment is required at the time of booking.</p>
    
    <h2>2. Cancellations & Refunds</h2>
    <p>Cancellations must be submitted in writing. Refund structure:</p>
    <ul>
        <li>More than 60 days before departure: Full refund minus deposit</li>
        <li>30-60 days before departure: 50% refund</li>
        <li>Less than 30 days before departure: No refund</li>
    </ul>
    <p>We strongly recommend purchasing comprehensive travel insurance to cover unforeseen cancellations.</p>
    
    <h2>3. Travel Insurance</h2>
    <p>All travelers are required to have valid travel insurance covering medical expenses, trip cancellation, and personal liability for the duration of the tour.</p>
    
    <h2>4. Passports & Visas</h2>
    <p>It is the traveler's responsibility to ensure their passport is valid for at least 6 months beyond the return date and to obtain all necessary visas and permits.</p>
    
    <h2>5. Health & Fitness</h2>
    <p>Some tours involve physically demanding activities. Travelers must disclose any medical conditions that may affect their participation. We reserve the right to refuse participation if a traveler's health poses a risk.</p>
    
    <h2>6. Changes to Itinerary</h2>
    <p>While we make every effort to adhere to the published itinerary, circumstances beyond our control (weather, political conditions, etc.) may require changes. We will endeavor to provide suitable alternatives.</p>
    
    <h2>7. Liability</h2>
    <p>Bamba Adventures acts as an agent for various service providers (hotels, airlines, ground operators). We are not liable for the acts, errors, or omissions of these third parties.</p>
    
    <h2>8. Photography & Media</h2>
    <p>We may take photographs during tours for promotional purposes. If you do not wish to be photographed, please inform your tour guide.</p>
    
    <h2>9. Governing Law</h2>
    <p>These terms are governed by the laws of Kenya. Any disputes shall be resolved through arbitration in Nairobi.</p>
    
    <h2>10. Contact</h2>
    <p>For questions about these terms, please contact us at <?php echo esc(getSetting('email') ?: 'info@bambaadventures.com'); ?>.</p>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
