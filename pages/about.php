<?php
require_once __DIR__ . '/../includes/functions.php';
$pageTitle = 'About Us | Bamba Adventures';
$pageDescription = 'Over a decade of crafting extraordinary journeys across Africa and beyond. Learn about Bamba Adventures\' story, values, and team.';
include __DIR__ . '/../includes/header.php';
?>

<style>
    .page-hero { min-height: 300px; position: relative; display: flex; align-items: flex-end; padding-top: 75px; background: linear-gradient(135deg, var(--primary-dark), var(--primary)); }
    .page-hero-content { position: relative; z-index: 2; padding: 4rem 5% 3.5rem; max-width: 1400px; margin: 0 auto; width: 100%; }
    .page-tag { display: inline-block; background: var(--accent); color: var(--text-dark); font-size: 0.78rem; font-weight: 700; letter-spacing: 3px; text-transform: uppercase; padding: 0.35rem 1rem; border-radius: 20px; margin-bottom: 1rem; }
    .page-hero-content h1 { font-size: clamp(2.2rem, 5vw, 4rem); color: var(--white); line-height: 1.1; text-shadow: 2px 2px 12px rgba(0,0,0,0.4); margin-bottom: 0.5rem; }
    .page-hero-content p { font-size: 1.1rem; color: rgba(255,255,255,0.9); max-width: 580px; }

    .section { padding: 5rem 5%; max-width: 1400px; margin: 0 auto; }
    .two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 5rem; align-items: center; }
    .two-col img { width: 100%; border-radius: 20px; box-shadow: 0 20px 50px rgba(0,0,0,0.12); }
    .section-tag { color: var(--primary); font-weight: 600; text-transform: uppercase; letter-spacing: 3px; font-size: 0.85rem; display: block; margin-bottom: 1rem; }
    h2.section-title { font-size: clamp(1.8rem, 3.5vw, 2.6rem); margin-bottom: 1.5rem; color: var(--text-dark); }
    .content-text p { color: var(--text-light); line-height: 1.9; font-size: 1rem; margin-bottom: 1.2rem; }

    .stats-row { background: linear-gradient(135deg, var(--primary-dark), var(--primary)); padding: 4rem 5%; text-align: center; color: var(--white); }
    .stats-inner { max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: repeat(4, 1fr); gap: 2rem; }
    .stat h3 { font-size: 2.8rem; color: var(--accent); margin-bottom: 0.4rem; }
    .stat p { font-size: 1rem; opacity: 0.9; }

.values-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 2rem; margin-top: 3rem; }
    .value-card { background: var(--white); border-radius: 20px; padding: 2.5rem; box-shadow: 0 5px 20px rgba(0,0,0,0.06); text-align: center; border-bottom: 4px solid transparent; transition: all 0.3s; }
    .value-card:hover { border-bottom-color: var(--accent); transform: translateY(-6px); }
    .value-icon { width: 70px; height: 70px; background: linear-gradient(135deg, var(--primary-dark), var(--primary)); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; color: var(--white); font-size: 1.6rem; }
    .value-card h3 { font-size: 1.15rem; margin-bottom: 0.75rem; color: var(--primary-dark); }
    .value-card p { font-size: 0.9rem; color: var(--text-light); line-height: 1.7; }

    .cta-strip { background: linear-gradient(135deg, var(--primary-dark), var(--primary)); padding: 4.5rem 5%; text-align: center; color: var(--white); }
    .cta-strip h2 { font-size: clamp(1.8rem, 3vw, 2.5rem); margin-bottom: 1rem; }
    .cta-strip p { font-size: 1.1rem; margin-bottom: 2rem; opacity: 0.9; max-width: 560px; margin-left: auto; margin-right: auto; }
    .btn-gold { background: var(--accent); color: var(--text-dark); padding: 0.9rem 2rem; border-radius: 30px; text-decoration: none; font-weight: 600; transition: all 0.3s; display: inline-block; }
    .btn-gold:hover { transform: translateY(-3px); ; }

    .fade-in { opacity: 0; transform: translateY(28px); transition: all 0.8s ease; }
    .fade-in.visible { opacity: 1; transform: translateY(0); }

    @media(max-width: 1100px) { .two-col { grid-template-columns: 1fr; gap: 2.5rem; } .values-grid { grid-template-columns: repeat(2, 1fr); } .stats-inner { grid-template-columns: repeat(2, 1fr); } }
    @media(max-width: 640px) { .values-grid { grid-template-columns: 1fr; } .stats-inner { grid-template-columns: 1fr 1fr; } }
</style>

<div class="page-hero">
    <div class="page-hero-content">
        <span class="page-tag">Our Story</span>
        <h1>About Bamba Adventures</h1>
        <p>Over a decade of crafting extraordinary journeys across Africa and beyond</p>
    </div>
</div>

<div class="stats-row">
    <div class="stats-inner">
        <div class="stat fade-in"><h3>12+</h3><p>Years of Experience</p></div>
        <div class="stat fade-in"><h3>5,000+</h3><p>Happy Travellers</p></div>
        <div class="stat fade-in"><h3>50+</h3><p>Destinations Covered</p></div>
        <div class="stat fade-in"><h3>98%</h3><p>Satisfaction Rate</p></div>
    </div>
</div>

<div class="section">
    <div class="two-col fade-in">
        <div>
            <img src="https://images.unsplash.com/photo-1549366021-9f761d450615?auto=format&fit=crop&w=900&q=80" alt="Bamba Adventures — safari experience">
        </div>
        <div class="content-text">
            <span class="section-tag">Who We Are</span>
            <h2 class="section-title">Born in Kenya, Built for the World</h2>
            <p>Bamba Adventures is a Nairobi-based travel company with over a decade of experience crafting personalised journeys across Africa and beyond. Founded on a deep love for the continent and its extraordinary landscapes, we have grown from a small safari outfit into a full-service travel agency trusted by thousands of travellers from around the world.</p>
            <p>We specialise in Kenya safaris, East African adventures, beach escapes, and international holiday packages — designing each itinerary from scratch to match the traveller, not a template. Whether you are after the Big Five in the Maasai Mara, a honeymoon on the Kenyan coast, or a group tour through the heart of Africa, our team handles every detail with precision and care.</p>
            <p>What sets us apart is simple: we are locals. Our guides, planners, and partners are rooted in the destinations we sell. That insider knowledge — the best camp in a specific season, the hidden beach, the community-run lodge worth supporting — is something you cannot get from a travel brochure. It is what makes a Bamba journey genuinely different.</p>
        </div>
    </div>
</div>

<div class="section" style="padding-top: 0;">
    <div style="text-align: center; margin-bottom: 3rem;">
        <span class="section-tag">What Drives Us</span>
        <h2 class="section-title">Our Core Values</h2>
    </div>
    <div class="values-grid">
        <div class="value-card fade-in">
            <div class="value-icon"><i class="fas fa-heart"></i></div>
            <h3>Passion for Travel</h3>
            <p>Every member of our team is a traveller first. We sell experiences we love and destinations we know inside out.</p>
        </div>
        <div class="value-card fade-in">
            <div class="value-icon"><i class="fas fa-handshake"></i></div>
            <h3>Honest Service</h3>
            <p>No hidden fees. No empty promises. We give you real advice, transparent pricing, and support from booking to return.</p>
        </div>
        <div class="value-card fade-in">
            <div class="value-icon"><i class="fas fa-leaf"></i></div>
            <h3>Responsible Tourism</h3>
            <p>We partner with eco-certified lodges, support local communities, and promote low-impact travel across all our programmes.</p>
        </div>
        <div class="value-card fade-in">
            <div class="value-icon"><i class="fas fa-star"></i></div>
            <h3>Attention to Detail</h3>
            <p>From the first enquiry to the final transfer, every detail is managed with care &mdash; so nothing is left to chance.</p>
        </div>
        <div class="value-card fade-in">
            <div class="value-icon"><i class="fas fa-globe-africa"></i></div>
            <h3>Local Knowledge</h3>
            <p>Born and based in Kenya, we bring authentic insider knowledge that no international agency can replicate.</p>
        </div>
        <div class="value-card fade-in">
            <div class="value-icon"><i class="fas fa-users"></i></div>
            <h3>Community First</h3>
            <p>We hire locally, work with community-owned camps, and ensure that tourism benefits the people and places we visit.</p>
        </div>
    </div>
</div>

<div class="cta-strip fade-in">
    <h2>Ready to Start Your Journey?</h2>
    <p>Talk to our team and let us design the perfect adventure for you.</p>
    <a href="/book" class="btn-gold">Plan My Trip</a>
</div>

<script>
const observer = new IntersectionObserver(entries => entries.forEach(e => { if (e.isIntersecting) e.target.classList.add('visible'); }), { threshold: 0.1 });
document.querySelectorAll('.fade-in').forEach(el => observer.observe(el));
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
