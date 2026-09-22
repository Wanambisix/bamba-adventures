/* ============================================
   BAMBA ADVENTURES — MAIN SCRIPTS
   ============================================ */

(function() {
    'use strict';

    // ----- NAVBAR SCROLL EFFECT -----
    const nav = document.querySelector('nav');
    if (nav) {
        window.addEventListener('scroll', () => {
            nav.classList.toggle('scrolled', window.scrollY > 20);
        });
    }

    // ----- MOBILE MENU -----
    const mobileToggle = document.querySelector('.mobile-toggle');
    const navLinks = document.querySelector('.nav-links');
    if (mobileToggle && navLinks) {
        mobileToggle.addEventListener('click', () => {
            navLinks.style.display = navLinks.style.display === 'flex' ? 'none' : 'flex';
            if (navLinks.style.display === 'flex') {
                navLinks.style.position = 'absolute';
                navLinks.style.top = '75px';
                navLinks.style.left = '0';
                navLinks.style.right = '0';
                navLinks.style.flexDirection = 'column';
                navLinks.style.background = '#fff';
                navLinks.style.padding = '1.5rem';
                navLinks.style.boxShadow = '0 8px 30px rgba(0,0,0,0.08)';
                navLinks.style.gap = '1rem';
                navLinks.style.zIndex = '999';
            }
        });
    }

    // ----- BACK TO TOP -----
    const backToTop = document.querySelector('.back-to-top');
    if (backToTop) {
        window.addEventListener('scroll', () => {
            backToTop.classList.toggle('visible', window.scrollY > 500);
        });
        backToTop.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    // ----- FAQ ACCORDION -----
    document.querySelectorAll('.faq-question').forEach(btn => {
        btn.addEventListener('click', () => {
            const item = btn.closest('.faq-item');
            const isOpen = item.classList.contains('open');
            document.querySelectorAll('.faq-item').forEach(i => i.classList.remove('open'));
            if (!isOpen) item.classList.add('open');
        });
    });

    // ----- TOUR DETAIL TABS -----
    document.querySelectorAll('.tour-detail-tabs button').forEach(tab => {
        tab.addEventListener('click', () => {
            const target = tab.dataset.tab;
            document.querySelectorAll('.tour-detail-tabs button').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab-panel').forEach(p => p.style.display = 'none');
            tab.classList.add('active');
            const panel = document.getElementById(target);
            if (panel) panel.style.display = 'block';
        });
    });
    // Activate first tab by default
    const firstTab = document.querySelector('.tour-detail-tabs button');
    if (firstTab) firstTab.click();

    // ----- NEWSLETTER FORM -----
    const newsletterForm = document.querySelector('.newsletter-form');
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const input = newsletterForm.querySelector('input[name="email"]');
            const btn = newsletterForm.querySelector('button');
            const email = input.value.trim();
            if (!email) return;
            btn.disabled = true;
            btn.textContent = 'Subscribing...';
            try {
                const res = await fetch('/api/api.php?action=subscribe', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'email=' + encodeURIComponent(email)
                });
                const data = await res.json();
                if (data.success) {
                    input.value = '';
                    btn.textContent = 'Subscribed!';
                    btn.style.background = '#10b981';
                    setTimeout(() => { btn.textContent = 'Subscribe'; btn.style.background = ''; btn.disabled = false; }, 3000);
                } else {
                    alert(data.error || 'Subscription failed');
                    btn.disabled = false;
                    btn.textContent = 'Subscribe';
                }
            } catch (err) {
                alert('Network error. Please try again.');
                btn.disabled = false;
                btn.textContent = 'Subscribe';
            }
        });
    }

    // ----- CONTACT FORM -----
    const contactForm = document.getElementById('contactForm');
    if (contactForm) {
        contactForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = contactForm.querySelector('button[type="submit"]');
            btn.disabled = true;
            btn.textContent = 'Sending...';
            const formData = new FormData(contactForm);
            formData.append('page_url', window.location.href);
            try {
                const res = await fetch('/api/api.php?action=contact', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();
                if (data.success) {
                    contactForm.reset();
                    alert(data.message);
                } else {
                    alert(data.error || 'Failed to send message');
                }
            } catch (err) {
                alert('Network error. Please try again.');
            }
            btn.disabled = false;
            btn.textContent = 'Send Message';
        });
    }

    // ----- SCROLL ANIMATIONS (simple fade-in) -----
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, { threshold: 0.1 });

    document.querySelectorAll('.card, .feature, .testimonial, .destination-card').forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(20px)';
        el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(el);
    });

})();
