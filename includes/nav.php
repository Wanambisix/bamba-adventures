<?php
/**
 * Shared Navigation Component
 * 
 * Self-contained nav that works on any PHP page.
 * Pulls destinations/services from DB when available.
 * Links to dynamic pages for live, admin-managed content.
 */
require_once __DIR__ . '/functions.php';

$siteName = getSetting('site_name', 'Bamba Adventures');
$logo = getSetting('logo', '/assets/images/bamba-logo.png');
$announcement = getSetting('announcement_bar', '');

$destinations = getDestinations();
$services = getServices();

function navIsActive($path) {
    $currentUri = $_SERVER['REQUEST_URI'] ?? '/';
    if ($path === '/') return $currentUri === '/' || $currentUri === '/index.php';
    return strpos($currentUri, $path) === 0;
}
?>
<style>
/* ===== SHARED NAV STYLES ===== */
nav { position: fixed; top: 0; left: 0; width: 100%; z-index: 1000; background: var(--white); box-shadow: 0 2px 20px rgba(0,0,0,0.08); padding: 0 5%; }
.nav-container { max-width: 1400px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center; height: 75px; }
.logo { height: 55px; transition: transform 0.3s ease; }
.logo:hover { transform: scale(1.05); }
.nav-links { display: flex; gap: 0; list-style: none; align-items: center; }
.nav-links > li { position: relative; }
.nav-links > li > a { text-decoration: none; color: var(--primary-dark); font-weight: 600; font-size: 0.88rem; padding: 0 1.1rem; height: 75px; display: flex; align-items: center; gap: 0.3rem; transition: color 0.3s ease, background 0.3s ease; white-space: nowrap; }
.nav-links > li > a:hover, .nav-links > li:hover > a { color: var(--primary); background: rgba(132,30,34,0.07); }
.nav-links > li > a.active { color: #000 !important; }
.nav-links > li > a.active::after { background: #000; }
.nav-links > li > a::after { content: ''; position: absolute; bottom: 0; left: 0; width: 0; height: 3px; background: var(--accent); transition: width 0.3s ease; }
.nav-links > li > a:hover::after, .nav-links > li:hover > a::after { width: 100%; }
.nav-links > li > a .chevron { font-size: 0.7rem; transition: transform 0.3s ease; }
.nav-links > li:hover > a .chevron { transform: rotate(180deg); }
.dropdown-menu { display: none; position: absolute; top: 75px; left: 0; background: var(--white); border-radius: 0 0 12px 12px; box-shadow: 0 15px 40px rgba(0,0,0,0.2); min-width: 260px; z-index: 999; overflow: hidden; border-top: 3px solid var(--accent); white-space: nowrap; }
.nav-links > li:hover .dropdown-menu { display: block; }
.dropdown-menu a { display: block; padding: 0.8rem 1.5rem; color: var(--text-dark); text-decoration: none; font-size: 0.88rem; transition: all 0.25s ease; border-bottom: 1px solid #f5f5f5; }
.dropdown-menu a:last-child { border-bottom: none; }
.dropdown-menu a:hover { background: var(--bg-light); color: var(--primary-dark); padding-left: 2rem; }
.dropdown-menu a i { width: 22px; color: var(--primary); margin-right: 0.4rem; }
.btn-primary { background: var(--accent) !important; color: var(--text-dark) !important; padding: 0.6rem 1.4rem !important; border-radius: 30px; font-weight: 600; font-size: 0.88rem !important; height: auto !important; transition: transform 0.3s ease, box-shadow 0.3s ease; }
.btn-primary:hover { transform: translateY(-2px); }
.btn-primary::after { display: none !important; }
.mobile-menu { display: none; font-size: 1.5rem; color: var(--primary-dark); cursor: pointer; }
.announcement-bar { background: var(--primary-dark); color: var(--white); text-align: center; padding: 0.5rem; font-size: 0.85rem; }
.announcement-bar a { color: var(--accent); font-weight: 600; text-decoration: underline; }
@media(max-width: 968px) {
    .nav-links { display: none; }
    .mobile-menu { display: block; }
    .nav-links.mobile-open { display: flex; position: absolute; top: 75px; left: 0; width: 100%; flex-direction: column; background: var(--primary-dark); padding: 1.5rem 5%; box-shadow: 0 10px 30px rgba(0,0,0,0.3); gap: 0; }
    .nav-links.mobile-open > li > a { height: auto; padding: 0.75rem 0; border-bottom: 1px solid rgba(255,255,255,0.1); color: var(--white); }
    .nav-links.mobile-open .dropdown-menu { position: static; display: none !important; box-shadow: none; border-radius: 0; border-top: none; background: rgba(0,0,0,0.2); }
    .nav-links.mobile-open .dropdown-menu.mobile-dropdown-open { display: block !important; }
    .nav-links.mobile-open .dropdown-menu a { color: rgba(255,255,255,0.85); }
}
</style>

<?php if ($announcement): ?>
<div class="announcement-bar"><?php echo $announcement; ?></div>
<?php endif; ?>

<nav id="navbar">
    <div class="nav-container">
        <a href="/"><img src="<?php echo htmlspecialchars($logo); ?>" alt="<?php echo htmlspecialchars($siteName); ?>" class="logo"></a>
        <ul class="nav-links" id="navLinks">
            <li><a href="/" class="<?php echo navIsActive('/') ? 'active' : ''; ?>">Home</a></li>
            <li><a href="/country/kenya">Kenya</a></li>
            <li>
                <a href="#">International <i class="fas fa-chevron-down chevron"></i></a>
                <div class="dropdown-menu">
                    <?php if (!empty($destinations)): ?>
                        <?php foreach ($destinations as $d): ?>
                        <a href="/destination/<?php echo htmlspecialchars($d['slug']); ?>"><i class="fas fa-globe"></i> <?php echo htmlspecialchars($d['name']); ?></a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <a href="/destination/africa"><i class="fas fa-globe-africa"></i> Africa</a>
                        <a href="/destination/europe"><i class="fas fa-globe-europe"></i> Europe</a>
                        <a href="/destination/asia"><i class="fas fa-globe-asia"></i> Asia</a>
                        <a href="/destination/north-america"><i class="fas fa-globe-americas"></i> North America</a>
                        <a href="/destination/south-america"><i class="fas fa-globe-americas"></i> South America</a>
                        <a href="/destination/middle-east"><i class="fas fa-mosque"></i> Middle East</a>
                        <a href="/destination/oceania"><i class="fas fa-globe"></i> Oceania / Pacific</a>
                        <a href="/destination/antarctica"><i class="fas fa-snowflake"></i> Antarctica</a>
                    <?php endif; ?>
                </div>
            </li>
            <li>
                <a href="#">Services <i class="fas fa-chevron-down chevron"></i></a>
                <div class="dropdown-menu">
                    <?php if (!empty($services)): ?>
                        <?php foreach ($services as $s): ?>
                        <a href="/service/<?php echo htmlspecialchars($s['slug']); ?>"><i class="<?php echo htmlspecialchars($s['icon'] ?: 'fas fa-star'); ?>"></i> <?php echo htmlspecialchars($s['name']); ?></a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <a href="/service/air-ticketing"><i class="fas fa-plane-departure"></i> Air Ticketing</a>
                        <a href="/service/hotel-bookings"><i class="fas fa-hotel"></i> Hotel Bookings</a>
                        <a href="/service/airport-transfers"><i class="fas fa-shuttle-van"></i> Airport Transfers</a>
                        <a href="/service/honeymoon-packages"><i class="fas fa-heart"></i> Honeymoon Packages</a>
                        <a href="/service/hikes-day-trips"><i class="fas fa-hiking"></i> Hikes &amp; Day Trips</a>
                        <a href="/service/corporate-team-building"><i class="fas fa-users"></i> Corporate Team Building</a>
                        <a href="/service/bush-safari-packages"><i class="fas fa-paw"></i> Bush Safari Packages</a>
                        <a href="/service/beach-packages"><i class="fas fa-umbrella-beach"></i> Beach Packages</a>
                        <a href="/service/international-holiday"><i class="fas fa-globe"></i> International Holiday</a>
                    <?php endif; ?>
                </div>
            </li>
            <li>
                <a href="#">Company <i class="fas fa-chevron-down chevron"></i></a>
                <div class="dropdown-menu">
                    <a href="/about"><i class="fas fa-info-circle"></i> About Us</a>
                    <a href="/blog"><i class="fas fa-blog"></i> Travel Blog</a>
                </div>
            </li>
            <li><a href="/faq">FAQ</a></li>
            <li><a href="/tours/category/volunteer">Volunteer Package</a></li>
            <li style="margin-left:1.2rem"><a href="/book" class="btn-primary">Enquire Now</a></li>
        </ul>
        <div class="mobile-menu" id="mobileMenuBtn"><i class="fas fa-bars"></i></div>
    </div>
</nav>

<script>
document.getElementById('mobileMenuBtn').addEventListener('click', function() {
    var nav = document.getElementById('navLinks');
    nav.classList.toggle('mobile-open');
    // Close all dropdowns when the whole menu is closed
    if (!nav.classList.contains('mobile-open')) {
        nav.querySelectorAll('.dropdown-menu').forEach(function(d) {
            d.classList.remove('mobile-dropdown-open');
        });
        nav.querySelectorAll('.chevron').forEach(function(c) {
            c.style.transform = '';
        });
    }
});

// Per-dropdown toggle on mobile
document.querySelectorAll('#navLinks > li > a').forEach(function(link) {
    link.addEventListener('click', function(e) {
        var nav = document.getElementById('navLinks');
        if (!nav.classList.contains('mobile-open')) return; // desktop: do nothing
        var dropdown = this.parentElement.querySelector('.dropdown-menu');
        if (!dropdown) return; // no dropdown, follow the link normally
        e.preventDefault();
        var isOpen = dropdown.classList.contains('mobile-dropdown-open');
        // Close all other dropdowns
        nav.querySelectorAll('.dropdown-menu').forEach(function(d) { d.classList.remove('mobile-dropdown-open'); });
        nav.querySelectorAll('.chevron').forEach(function(c) { c.style.transform = ''; });
        // Toggle clicked one
        if (!isOpen) {
            dropdown.classList.add('mobile-dropdown-open');
            var chevron = this.querySelector('.chevron');
            if (chevron) chevron.style.transform = 'rotate(180deg)';
        }
    });
});
</script>
