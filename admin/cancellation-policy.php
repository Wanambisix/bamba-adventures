<?php
ob_start();
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

// Find existing page record
$stmt = $pdo->prepare("SELECT id FROM pages WHERE slug = 'cancellation-policy' LIMIT 1");
$stmt->execute();
$row = $stmt->fetch();

if ($row) {
    // Page already exists — open it for editing
    ob_end_clean();
    header('Location: /admin/page-edit.php?id=' . $row['id']);
    exit;
}

// Page doesn't exist yet — create it with placeholder content so the editor opens cleanly
$defaultContent = '<h2>1. Cancellation by the Client</h2>
<p>If you wish to cancel your booking, notice must be provided in writing to info@bambaadventures.co.ke. Cancellation charges are calculated based on the number of days between the date of written notice and the scheduled departure date:</p>
<ul>
  <li><strong>45 days or more:</strong> Full refund less a $50 administration fee.</li>
  <li><strong>30 – 44 days:</strong> 25% cancellation charge.</li>
  <li><strong>15 – 29 days:</strong> 50% cancellation charge.</li>
  <li><strong>8 – 14 days:</strong> 75% cancellation charge.</li>
  <li><strong>7 days or fewer / No-show:</strong> No refund.</li>
</ul>

<h2>2. Cancellation by Bamba Adventures</h2>
<p>If Bamba Adventures cancels a tour due to circumstances beyond our control, clients will receive a full refund or the option to transfer to an alternative tour of equal value.</p>

<h2>3. Force Majeure</h2>
<p>In the event of cancellation due to force majeure (acts of God, pandemic, civil unrest, government travel bans etc.), Bamba Adventures will offer alternative dates or a credit note valid for 24 months.</p>

<h2>4. Travel Insurance</h2>
<p>We strongly recommend all clients purchase comprehensive travel insurance covering trip cancellation, medical emergencies, and baggage loss.</p>

<h2>5. Refunds</h2>
<p>Eligible refunds are processed within 14 business days via the original payment method. Non-refundable components (park fees, permits, pre-purchased flights) are excluded from any refund.</p>

<h2>6. How to Cancel</h2>
<p>Email your cancellation request including your booking reference to info@bambaadventures.co.ke. We will acknowledge within 1 business day and confirm any applicable charges.</p>';

$pdo->prepare("INSERT INTO pages (title, slug, content, icon, show_in_nav, meta_title, meta_description, status, sort_order)
               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)")
    ->execute([
        'Cancellation Policy',
        'cancellation-policy',
        $defaultContent,
        'fas fa-file-contract',
        'no',
        'Cancellation Policy | Bamba Adventures',
        'Read the Bamba Adventures cancellation and refund policy for all tour bookings.',
        'active',
        99,
    ]);

$newId = $pdo->lastInsertId();
ob_end_clean();
header('Location: /admin/page-edit.php?id=' . $newId);
exit;
