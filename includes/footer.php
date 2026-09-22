<?php
$siteName = getSetting('site_name', 'Bamba Adventures');
$whatsapp = getSetting('contact_whatsapp', '254706606606');
$destinations = getDestinations();
?>

<style>
    footer { background: #1a1a1a; color: var(--white); padding: 5rem 5% 2rem; }
    .footer-grid { max-width: 1400px; margin: 0 auto; display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 4rem; margin-bottom: 4rem; }
    .footer-col h4 { color: var(--accent); margin-bottom: 1.5rem; font-size: 1.15rem; }
    .footer-col p { color: #aaa; line-height: 1.8; margin-bottom: 1.5rem; }
    .footer-links { list-style: none; }
    .footer-links li { margin-bottom: 0.75rem; }
    .footer-links a { color: #aaa; text-decoration: none; transition: color 0.3s ease; display: flex; align-items: flex-start; gap: 0.6rem; line-height: 1.5; font-size: 0.9rem; }
    .footer-links a:hover { color: var(--accent); }
    .footer-links a i { color: var(--primary); margin-top: 0.2rem; flex-shrink: 0; width: 16px; }
    .social-links { display: flex; gap: 0.75rem; margin-top: 1.5rem; flex-wrap: wrap; }
    .social-links a { width: 40px; height: 40px; background: rgba(255,255,255,0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--white); transition: all 0.3s ease; text-decoration: none; font-size: 0.9rem; }
    .social-links a:hover { background: var(--accent); color: var(--text-dark); transform: translateY(-3px); }
    .footer-bottom { border-top: 1px solid #333; padding-top: 2rem; text-align: center; color: #666; font-size: 0.88rem; max-width: 1400px; margin: 0 auto; }
    .footer-bottom a { color: #999; text-decoration: none; transition: color 0.3s; }
    .footer-bottom a:hover { color: var(--accent); }
    @media(max-width: 1024px) { .footer-grid { grid-template-columns: 1fr 1fr; gap: 2rem; } }
    @media(max-width: 640px) { .footer-grid { grid-template-columns: 1fr; gap: 2rem; } }

    .floating-wrap { position: fixed; bottom: 2rem; right: 2rem; display: flex; flex-direction: column; gap: 0.75rem; z-index: 999; align-items: flex-end; }
    .back-to-top { width: 50px; height: 50px; background: var(--primary-dark); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 1.1rem; text-decoration: none; transition: all 0.3s ease; opacity: 0; pointer-events: none; cursor: pointer; border: none; }
    .back-to-top.visible { opacity: 1; pointer-events: all; }
    .back-to-top:hover { transform: translateY(-3px); background: var(--primary); }
    .whatsapp-btn { width: 58px; height: 58px; background: #25D366; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 1.7rem; text-decoration: none; transition: all 0.3s ease; position: relative; }
    .whatsapp-btn:hover { transform: scale(1.1); }
    .wa-tooltip { position: absolute; right: 70px; top: 50%; transform: translateY(-50%); background: #1a1a1a; color: #fff; font-size: 0.8rem; padding: 0.4rem 0.9rem; border-radius: 8px; white-space: nowrap; opacity: 0; pointer-events: none; transition: opacity 0.3s ease; }
    .whatsapp-btn:hover .wa-tooltip { opacity: 1; }
    
    
</style>

<footer id="contact">
    <div class="footer-grid">
        <div class="footer-col">
            <img src="/assets/images/bamba-logo.png" alt="<?php echo esc($siteName); ?>" style="height: 60px; margin-bottom: 1rem;">
            <p>Your trusted partner for extraordinary journeys across Africa, Asia, and the Middle East. Creating memories that last a lifetime.</p>
            <div class="social-links">
                <a href="https://www.facebook.com/bambaadventuresnevents/" target="_blank" rel="noopener" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                <a href="https://x.com/adventuresbamba" target="_blank" rel="noopener" aria-label="X (Twitter)" style="font-size:0.75rem;"><svg viewBox="0 0 24 24" fill="currentColor" width="15" height="15" style="display:block;"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.746l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg></a>
                <a href="https://www.instagram.com/bamba_adventures/" target="_blank" rel="noopener" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                <a href="https://www.youtube.com/channel/UCzI138kTQQiKC7rZSBDYi7A" target="_blank" rel="noopener" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
                <a href="https://www.google.com/maps/place//data=!4m2!3m1!1s0x182f1736edc5ee2d:0xc8c8ef5c278fa74c?source=g.page.share" target="_blank" rel="noopener" aria-label="Google Maps"><i class="fas fa-map-marker-alt"></i></a>
            </div>
        </div>

        <div class="footer-col">
            <h4>Destinations</h4>
            <ul class="footer-links">
                <?php foreach (array_slice($destinations, 0, 5) as $d): ?>
                <li><a href="/destination/<?php echo esc($d['slug']); ?>"><i class="fas fa-chevron-right"></i> <?php echo esc($d['name']); ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="footer-col">
            <h4>Company</h4>
            <ul class="footer-links">
                <li><a href="/about"><i class="fas fa-chevron-right"></i> About Us</a></li>
                <li><a href="/blog"><i class="fas fa-chevron-right"></i> Travel Blog</a></li>
                <li><a href="/page/careers"><i class="fas fa-chevron-right"></i> Careers</a></li>
                <li><a href="/tours/category/volunteer-package"><i class="fas fa-chevron-right"></i> Volunteer Package</a></li>
                <li><a href="/page/terms"><i class="fas fa-chevron-right"></i> Terms &amp; Conditions</a></li>
            </ul>
        </div>

        <div class="footer-col">
            <h4>Contact Us</h4>
            <ul class="footer-links">
                <li>
                    <a href="tel:+254706606606">
                        <i class="fas fa-phone"></i>
                        <span>+254 706 606 606<br>+254 708 808 808<br>+254 784 606 606</span>
                    </a>
                </li>
                <li>
                    <a href="mailto:info@bambaadventures.co.ke">
                        <i class="fas fa-envelope"></i>
                        info@bambaadventures.co.ke
                    </a>
                </li>
                <li>
                    <a href="https://www.google.com/maps/place//data=!4m2!3m1!1s0x182f1736edc5ee2d:0xc8c8ef5c278fa74c?source=g.page.share" target="_blank" rel="noopener">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>Ratanssi Educational Trust Building 2nd Floor, Nairobi</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <div class="footer-bottom">
        <p>&copy; <?php echo date('Y'); ?> <?php echo esc($siteName); ?>. All rights reserved. | Designed by <a href="http://www.avid.co.ke" target="_blank" rel="noopener">Avid Microsystems</a></p>
    </div>
</footer>

<div class="floating-wrap">
    <?php if ($whatsapp): ?>
    <a href="https://wa.me/<?php echo esc($whatsapp); ?>?text=Hello%20Bamba%20Adventures!%20I%20would%20like%20to%20enquire%20about%20your%20tours." target="_blank" rel="noopener" class="whatsapp-btn" aria-label="Chat on WhatsApp">
        <i class="fab fa-whatsapp"></i>
        <span class="wa-tooltip">Chat with us!</span>
    </a>
    <?php endif; ?>
    <button class="back-to-top" id="backToTop" onclick="window.scrollTo({top:0,behavior:'smooth'})" aria-label="Back to top"><i class="fas fa-arrow-up"></i></button>
</div>

<script src="/assets/js/main.js"></script>
<script>
const backToTop = document.getElementById('backToTop');
if (backToTop) {
    window.addEventListener('scroll', () => {
        backToTop.classList.toggle('visible', window.scrollY > 400);
    });
}
</script>
</body>
</html>
