<?php
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$navGroups = [
    'Overview' => [
        ['dashboard.php', 'fas fa-tachometer-alt', 'Dashboard'],
    ],
    'Content' => [
        ['tours.php', 'fas fa-globe', 'Tour Packages'],
        ['categories.php', 'fas fa-tags', 'Tour Categories'],
        ['destinations.php', 'fas fa-map-marked-alt', 'Destinations'],
        ['countries.php', 'fas fa-flag', 'Countries'],
        ['services.php', 'fas fa-concierge-bell', 'Services'],
        ['pages.php', 'fas fa-file-alt', 'Pages'],
        ['cancellation-policy.php', 'fas fa-file-contract', 'Cancellation Policy'],
        ['blog.php', 'fas fa-blog', 'Blog Posts'],
        ['testimonials.php', 'fas fa-quote-left', 'Testimonials'],
        ['gallery.php', 'fas fa-images', 'Gallery'],
        ['faq.php', 'fas fa-question-circle', 'FAQ'],
    ],
    'Operations' => [
        ['bookings.php', 'fas fa-calendar-check', 'Bookings'],
        ['inquiries.php', 'fas fa-envelope', 'Inquiries'],
        ['subscribers.php', 'fas fa-mail-bulk', 'Subscribers'],
        ['careers.php', 'fas fa-briefcase', 'Careers'],
    ],

    'System' => [
        ['settings.php', 'fas fa-cog', 'Site Settings'],
        ['backup.php', 'fas fa-database', 'Backup & Restore'],
    ],
];
?>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <img src="/assets/images/bamba-logo.png" alt="Bamba" style="filter:brightness(0) invert(1);">
        <span>Bamba Admin</span>
    </div>
    <ul class="sidebar-nav">
        <?php foreach ($navGroups as $groupName => $items): ?>
            <li class="nav-group"><?php echo $groupName; ?></li>
            <?php foreach ($items as [$url, $icon, $label]): ?>
            <li>
                <a href="<?php echo $url; ?>" class="<?php echo $currentPage === basename($url, '.php') ? 'active' : ''; ?>">
                    <i class="<?php echo $icon; ?>"></i> <?php echo $label; ?>
                </a>
            </li>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </ul>
    <div class="sidebar-footer">
        <a href="/" target="_blank" style="color:rgba(255,255,255,0.6);font-size:0.8rem;display:block;margin-bottom:0.5rem;"><i class="fas fa-external-link-alt"></i> View Website</a>
        <a href="logout.php" class="btn-logout" style="display:block;text-align:center;"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</aside>
<?php echo render_flash(); ?>
<script>window.BAMBA_CSRF = <?php echo json_encode(csrf_token()); ?>;</script>
<button class="mobile-toggle" onclick="document.getElementById('sidebar').classList.toggle('open')" style="display:none;position:fixed;top:1rem;left:1rem;z-index:1001;background:var(--primary);color:#fff;border:none;padding:0.5rem;border-radius:6px;">
    <i class="fas fa-bars"></i>
</button>
