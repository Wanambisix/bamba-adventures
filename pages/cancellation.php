<?php
require_once __DIR__ . '/../includes/functions.php';

// Load admin-edited content from CMS; fall back to built-in default
$cmsPage = getPageBySlug('cancellation-policy');
$adminContent = $cmsPage ? $cmsPage['content'] : null;

$pageTitle = 'Cancellation Policy | Bamba Adventures';
include __DIR__ . '/../includes/header.php';
?>

<style>
    .cp-hero {
        background: linear-gradient(135deg, var(--primary-dark), var(--primary));
        padding: 8rem 5% 4rem;
        text-align: center;
        color: var(--white);
        margin-top: 75px;
    }
    .cp-hero h1 { font-size: clamp(2rem, 4vw, 3rem); margin-bottom: 0.75rem; }
    .cp-hero p { opacity: 0.9; font-size: 1.05rem; max-width: 600px; margin: 0 auto; }

    .cp-wrap {
        max-width: 900px;
        margin: 0 auto;
        padding: 4rem 5% 5rem;
    }

    /* Last updated notice */
    .cp-updated {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: #fff8e6;
        border: 1px solid #fdb011;
        color: #7a5200;
        font-size: 0.85rem;
        padding: 0.5rem 1rem;
        border-radius: 30px;
        margin-bottom: 2.5rem;
    }
    .cp-updated i { color: #fdb011; }

    /* Summary table */
    .cp-summary {
        background: var(--bg-light);
        border-radius: 16px;
        overflow: hidden;
        margin-bottom: 3rem;
        box-shadow: 0 4px 16px rgba(0,0,0,0.06);
    }
    .cp-summary-header {
        background: var(--primary-dark);
        color: #fff;
        padding: 1rem 1.5rem;
        font-weight: 700;
        font-family: 'Poppins', sans-serif;
        font-size: 1.1rem;
        display: flex;
        align-items: center;
        gap: 0.6rem;
    }
    .cp-table { width: 100%; border-collapse: collapse; }
    .cp-table th { background: rgba(132,30,34,0.08); color: var(--primary-dark); font-size: 0.82rem; text-transform: uppercase; letter-spacing: 0.5px; padding: 0.8rem 1.25rem; text-align: left; }
    .cp-table td { padding: 0.85rem 1.25rem; font-size: 0.92rem; color: var(--text-light); border-top: 1px solid #e8e8e8; vertical-align: middle; }
    .cp-table tr:last-child td { border-bottom: none; }
    .cp-table tr:hover td { background: rgba(253,176,17,0.06); }
    .badge-full { background: #d1fae5; color: #065f46; padding: 0.2rem 0.65rem; border-radius: 20px; font-size: 0.8rem; font-weight: 600; white-space: nowrap; }
    .badge-partial { background: #fef3c7; color: #92400e; padding: 0.2rem 0.65rem; border-radius: 20px; font-size: 0.8rem; font-weight: 600; white-space: nowrap; }
    .badge-none { background: #fee2e2; color: #991b1b; padding: 0.2rem 0.65rem; border-radius: 20px; font-size: 0.8rem; font-weight: 600; white-space: nowrap; }

    /* Section blocks */
    .cp-section { margin-bottom: 2.5rem; }
    .cp-section h2 {
        font-family: 'Poppins', sans-serif;
        font-size: 1.5rem;
        color: var(--primary-dark);
        margin-bottom: 1rem;
        padding-bottom: 0.6rem;
        border-bottom: 2px solid #f0e0e0;
        display: flex;
        align-items: center;
        gap: 0.6rem;
    }
    .cp-section h2 i { font-size: 1.1rem; color: var(--accent); }
    .cp-section h3 { font-family: 'Poppins', sans-serif; font-size: 1.15rem; color: var(--primary); margin: 1.25rem 0 0.5rem; }
    .cp-section p { color: var(--text-light); line-height: 1.85; margin-bottom: 0.9rem; font-size: 0.95rem; }
    .cp-section ul { list-style: none; padding: 0; margin: 0.5rem 0 1rem; }
    .cp-section ul li { color: var(--text-light); font-size: 0.95rem; line-height: 1.75; padding: 0.35rem 0 0.35rem 1.6rem; position: relative; }
    .cp-section ul li::before { content: '›'; position: absolute; left: 0.4rem; color: var(--primary); font-weight: 700; font-size: 1.1rem; line-height: 1.5; }
    .cp-section ol { padding-left: 1.4rem; margin: 0.5rem 0 1rem; }
    .cp-section ol li { color: var(--text-light); font-size: 0.95rem; line-height: 1.75; margin-bottom: 0.4rem; }

    /* Highlight box */
    .cp-highlight {
        background: #fff8e6;
        border-left: 4px solid var(--accent);
        padding: 1rem 1.25rem;
        border-radius: 0 10px 10px 0;
        margin: 1rem 0;
        font-size: 0.92rem;
        color: #5a3e00;
        line-height: 1.7;
    }
    .cp-highlight i { color: var(--accent); margin-right: 0.4rem; }

    /* Info box */
    .cp-info {
        background: #eff6ff;
        border-left: 4px solid #3b82f6;
        padding: 1rem 1.25rem;
        border-radius: 0 10px 10px 0;
        margin: 1rem 0;
        font-size: 0.92rem;
        color: #1e3a5f;
        line-height: 1.7;
    }
    .cp-info i { color: #3b82f6; margin-right: 0.4rem; }

    /* Contact CTA */
    .cp-cta {
        background: linear-gradient(135deg, var(--primary-dark), var(--primary));
        border-radius: 20px;
        padding: 2.5rem;
        text-align: center;
        color: #fff;
        margin-top: 3rem;
    }
    .cp-cta h3 { font-family: 'Poppins', sans-serif; font-size: 1.5rem; margin-bottom: 0.6rem; }
    .cp-cta p { opacity: 0.9; font-size: 0.95rem; margin-bottom: 1.5rem; }
    .cp-cta-btns { display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap; }
    .cp-btn-gold { background: var(--accent); color: var(--text-dark); padding: 0.75rem 1.75rem; border-radius: 30px; text-decoration: none; font-weight: 700; font-size: 0.95rem; transition: all 0.3s; display: inline-flex; align-items: center; gap: 0.5rem; }
    .cp-btn-gold:hover { transform: translateY(-2px); }
    .cp-btn-outline { border: 2px solid rgba(255,255,255,0.6); color: #fff; padding: 0.75rem 1.75rem; border-radius: 30px; text-decoration: none; font-weight: 600; font-size: 0.95rem; transition: all 0.3s; display: inline-flex; align-items: center; gap: 0.5rem; }
    .cp-btn-outline:hover { background: rgba(255,255,255,0.15); border-color: #fff; }

    /* Rendered admin content */
    .admin-content h2 { font-family: 'Poppins', sans-serif; font-size: 1.5rem; color: var(--primary-dark); margin: 2rem 0 1rem; }
    .admin-content h3 { font-family: 'Poppins', sans-serif; font-size: 1.2rem; color: var(--primary); margin: 1.5rem 0 0.6rem; }
    .admin-content p { color: var(--text-light); line-height: 1.85; margin-bottom: 0.9rem; font-size: 0.95rem; }
    .admin-content ul, .admin-content ol { padding-left: 1.4rem; margin: 0.5rem 0 1rem; color: var(--text-light); font-size: 0.95rem; line-height: 1.8; }
    .admin-content li { margin-bottom: 0.4rem; }
    .admin-content strong { color: var(--text-dark); }
    .admin-content table { width: 100%; border-collapse: collapse; margin: 1rem 0; }
    .admin-content th { background: var(--bg-light); padding: 0.7rem 1rem; text-align: left; font-size: 0.85rem; color: var(--primary-dark); }
    .admin-content td { padding: 0.7rem 1rem; border-top: 1px solid #eee; color: var(--text-light); font-size: 0.92rem; }
</style>

<div class="cp-hero">
    <h1><i class="fas fa-file-contract" style="opacity:0.8;margin-right:0.5rem;"></i> Cancellation Policy</h1>
    <p>Please read our cancellation and refund terms carefully before confirming your booking.</p>
</div>

<div class="cp-wrap">

    <?php if ($adminContent): ?>
    <!-- ── Admin-edited content ── -->
    <div class="admin-content">
        <?= $adminContent ?>
    </div>

    <?php else: ?>
    <!-- ── Default built-in cancellation policy ── -->

    <div class="cp-updated">
        <i class="fas fa-calendar-check"></i>
        Last updated: <?= date('F Y') ?>
    </div>

    <!-- Quick-reference table -->
    <div class="cp-summary">
        <div class="cp-summary-header"><i class="fas fa-table"></i> Cancellation Summary</div>
        <table class="cp-table">
            <thead>
                <tr>
                    <th>Notice Period Before Departure</th>
                    <th>Cancellation Charge</th>
                    <th>Refund Status</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>45 days or more</strong></td>
                    <td>$50 administration fee only</td>
                    <td><span class="badge-full">Full Refund</span></td>
                </tr>
                <tr>
                    <td><strong>30 – 44 days</strong></td>
                    <td>25% of total tour cost</td>
                    <td><span class="badge-partial">75% Refund</span></td>
                </tr>
                <tr>
                    <td><strong>15 – 29 days</strong></td>
                    <td>50% of total tour cost</td>
                    <td><span class="badge-partial">50% Refund</span></td>
                </tr>
                <tr>
                    <td><strong>8 – 14 days</strong></td>
                    <td>75% of total tour cost</td>
                    <td><span class="badge-partial">25% Refund</span></td>
                </tr>
                <tr>
                    <td><strong>7 days or fewer</strong></td>
                    <td>100% of total tour cost</td>
                    <td><span class="badge-none">No Refund</span></td>
                </tr>
                <tr>
                    <td><strong>No-show</strong></td>
                    <td>100% of total tour cost</td>
                    <td><span class="badge-none">No Refund</span></td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="cp-highlight">
        <i class="fas fa-exclamation-triangle"></i>
        <strong>Important:</strong> All cancellations must be submitted in writing via email to <a href="mailto:info@bambaadventures.co.ke" style="color:inherit;font-weight:700;">info@bambaadventures.co.ke</a>. Cancellation is effective from the date Bamba Adventures receives and acknowledges your written notice.
    </div>

    <!-- Section 1 -->
    <div class="cp-section">
        <h2><i class="fas fa-ban"></i> 1. Cancellation by the Client</h2>
        <p>If you wish to cancel your booking, notice must be provided in writing. Cancellation charges are calculated based on the number of days between the date of written notice and the scheduled departure date:</p>
        <ul>
            <li><strong>45 days or more:</strong> A refund of the total amount paid, less a $50 non-refundable administration fee.</li>
            <li><strong>30 – 44 days:</strong> A cancellation charge of 25% of the total tour cost applies.</li>
            <li><strong>15 – 29 days:</strong> A cancellation charge of 50% of the total tour cost applies.</li>
            <li><strong>8 – 14 days:</strong> A cancellation charge of 75% of the total tour cost applies.</li>
            <li><strong>7 days or fewer / No-show:</strong> No refund will be issued.</li>
        </ul>
        <p>Refunds (where applicable) are processed within <strong>14 business days</strong> of the cancellation confirmation, via the original payment method.</p>
    </div>

    <!-- Section 2 -->
    <div class="cp-section">
        <h2><i class="fas fa-exchange-alt"></i> 2. Amendments & Date Changes</h2>
        <p>We understand that plans sometimes change. If you wish to amend your booking rather than cancel it entirely:</p>
        <ul>
            <li>Amendment requests must be made in writing at least <strong>30 days before departure</strong>.</li>
            <li>A <strong>$30 administration fee</strong> per amendment applies.</li>
            <li>Date changes are subject to availability and may attract rate differences if the new dates fall in a different pricing season.</li>
            <li>Amendments requested within 30 days of departure will be treated as a cancellation and re-booking under the standard cancellation terms above.</li>
        </ul>
    </div>

    <!-- Section 3 -->
    <div class="cp-section">
        <h2><i class="fas fa-times-circle"></i> 3. Cancellation by Bamba Adventures</h2>
        <p>Bamba Adventures reserves the right to cancel a tour in the following circumstances:</p>
        <ul>
            <li>Minimum group size is not met (where applicable) — you will receive a full refund or the option to join an alternative departure.</li>
            <li>Circumstances beyond our reasonable control, including but not limited to natural disasters, government travel advisories, civil unrest, or pandemic-related restrictions (force majeure).</li>
            <li>Safety concerns that make it impossible to safely operate the tour.</li>
        </ul>
        <p>In all cases where Bamba Adventures cancels a tour, clients will receive a <strong>full refund</strong> of all amounts paid, or the option to transfer to an alternative tour of equal value. We shall not be liable for any additional costs incurred by clients, such as airfares or accommodation booked independently.</p>
    </div>

    <!-- Section 4 -->
    <div class="cp-section">
        <h2><i class="fas fa-cloud-sun-rain"></i> 4. Force Majeure</h2>
        <p>Force majeure refers to unforeseeable events beyond the control of Bamba Adventures, including:</p>
        <ul>
            <li>Acts of God (earthquakes, floods, volcanic eruptions)</li>
            <li>Epidemic or pandemic restrictions</li>
            <li>War, terrorism, political instability, or civil unrest</li>
            <li>Government-imposed travel bans or border closures</li>
            <li>Strike or industrial action affecting transportation</li>
        </ul>
        <p>In the event of a force majeure cancellation, Bamba Adventures will endeavour to offer alternative dates or a credit note valid for 24 months. Cash refunds in force majeure situations are subject to any costs already committed to suppliers on your behalf, and may take longer to process.</p>
        <div class="cp-info">
            <i class="fas fa-info-circle"></i>
            We strongly recommend purchasing comprehensive travel insurance that covers cancellation due to unforeseen circumstances, including medical emergencies, force majeure events, and trip interruption.
        </div>
    </div>

    <!-- Section 5 -->
    <div class="cp-section">
        <h2><i class="fas fa-shield-alt"></i> 5. Travel Insurance</h2>
        <p>Bamba Adventures <strong>strongly recommends</strong> that all clients take out comprehensive travel insurance at the time of booking. Your policy should include:</p>
        <ul>
            <li>Trip cancellation and curtailment cover</li>
            <li>Medical expenses and emergency evacuation</li>
            <li>Baggage loss or delay</li>
            <li>Personal liability</li>
            <li>Cover for adventure activities included in your tour</li>
        </ul>
        <p>Failure to hold adequate travel insurance is at the client's own risk. Bamba Adventures accepts no liability for losses that would otherwise be recoverable under a travel insurance policy.</p>
    </div>

    <!-- Section 6 -->
    <div class="cp-section">
        <h2><i class="fas fa-credit-card"></i> 6. Refund Processing</h2>
        <p>Where a refund is approved, please note the following:</p>
        <ul>
            <li>Refunds are issued within <strong>14 business days</strong> of the cancellation being confirmed in writing.</li>
            <li>Refunds are returned via the <strong>original payment method</strong> used at the time of booking.</li>
            <li>Bank transfer fees and foreign exchange losses incurred during refund processing are borne by the client.</li>
            <li>Non-refundable components (e.g. park fees, permits, domestic flights pre-purchased on your behalf) will be deducted from any refund owed, regardless of the notice period.</li>
        </ul>
    </div>

    <!-- Section 7 -->
    <div class="cp-section">
        <h2><i class="fas fa-pencil-alt"></i> 7. How to Submit a Cancellation</h2>
        <p>To cancel your booking, please follow these steps:</p>
        <ol>
            <li>Send a written cancellation request to <a href="mailto:info@bambaadventures.co.ke" style="color:var(--primary-dark);font-weight:600;">info@bambaadventures.co.ke</a> with your booking reference number.</li>
            <li>Our team will acknowledge your request within 1 business day and confirm the applicable cancellation charges.</li>
            <li>Once confirmed, any eligible refund will be processed within 14 business days.</li>
        </ol>
        <div class="cp-highlight">
            <i class="fas fa-phone"></i>
            For urgent cancellations or queries, you may also reach us on <strong>+254 706 606 606</strong> or via WhatsApp — however, written confirmation by email is still required to initiate the cancellation process.
        </div>
    </div>

    <!-- Section 8 -->
    <div class="cp-section">
        <h2><i class="fas fa-gavel"></i> 8. Governing Law</h2>
        <p>This Cancellation Policy is governed by and construed in accordance with the laws of the Republic of Kenya. Any disputes arising from bookings made with Bamba Adventures shall be subject to the exclusive jurisdiction of the Kenyan courts.</p>
        <p>By completing a booking with Bamba Adventures, you confirm that you have read, understood, and agreed to this Cancellation Policy in its entirety.</p>
    </div>

    <?php endif; ?>

    <!-- Contact CTA — shown regardless of content source -->
    <div class="cp-cta">
        <h3>Questions About Your Booking?</h3>
        <p>Our team is happy to help you understand your options before making any decisions.</p>
        <div class="cp-cta-btns">
            <a href="mailto:info@bambaadventures.co.ke" class="cp-btn-gold"><i class="fas fa-envelope"></i> Email Us</a>
            <a href="https://wa.me/254706606606?text=Hello!%20I%20have%20a%20question%20about%20your%20cancellation%20policy." target="_blank" rel="noopener" class="cp-btn-outline"><i class="fab fa-whatsapp"></i> WhatsApp</a>
            <a href="/book" class="cp-btn-outline"><i class="fas fa-calendar-check"></i> Book a Tour</a>
        </div>
    </div>

</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
