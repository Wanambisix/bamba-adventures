<?php
require_once __DIR__ . '/../includes/functions.php';
$pageTitle = 'Careers | Bamba Adventures';
$pageDescription = 'Join the Bamba Adventures team. Explore current job openings.';

$careers = getCareers();
include __DIR__ . '/../includes/header.php';
?>

<style>
.career-card { background: var(--white); border-radius: var(--radius); padding: 1.8rem; box-shadow: var(--shadow); margin-bottom: 1.5rem; transition: var(--transition); }
.career-card:hover { transform: translateY(-3px); box-shadow: var(--shadow-lg); }
.career-card h3 { font-family: var(--font-serif); color: var(--primary-dark); margin-bottom: 0.3rem; }
.career-meta { display: flex; gap: 1rem; font-size: 0.8rem; color: var(--text-light); margin-bottom: 0.8rem; }
.career-card p { color: var(--text-light); font-size: 0.9rem; line-height: 1.6; margin-bottom: 1rem; }
.careers-wrap { max-width: 900px; margin: 0 auto; padding: 2rem 5% 6rem; }
.apply-form { display: none; margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--border); }
.apply-form.open { display: block; }
</style>

<div class="page-hero">
    <h1>Join Our Team</h1>
    <p>Passionate about travel? We are always looking for exceptional people.</p>
</div>

<section class="section" style="padding-top:2rem;">
    <div class="careers-wrap">
        <?php if ($careers): foreach ($careers as $c): ?>
        <div class="career-card">
            <h3><?php echo esc($c['title']); ?></h3>
            <div class="career-meta">
                <span><i class="fas fa-map-marker-alt"></i> <?php echo esc($c['location'] ?? 'Nairobi, Kenya'); ?></span>
                <span><i class="fas fa-clock"></i> <?php echo esc($c['type'] ?? 'Full-time'); ?></span>
            </div>
            <p><?php echo esc(truncate($c['description'] ?? '', 150)); ?></p>
            <a href="javascript:void(0)" class="btn btn-primary" style="padding:0.6rem 1.5rem; font-size:0.8rem;" onclick="this.nextElementSibling.classList.toggle('open');">Apply Now</a>
            <div class="apply-form">
                <form method="POST" action="/api/api.php?action=career_apply" enctype="multipart/form-data">
            <?php echo csrf_field(); ?>
                    <input type="hidden" name="career_id" value="<?php echo $c['id']; ?>">
                    <div class="form-row">
                        <div class="form-group"><label>Full Name *</label><input type="text" name="name" required></div>
                        <div class="form-group"><label>Email *</label><input type="email" name="email" required></div>
                    </div>
                    <div class="form-row">
                        <div class="form-group"><label>Phone</label><input type="tel" name="phone"></div>
                        <div class="form-group"><label>Resume (PDF/DOC)</label><input type="file" name="resume" accept=".pdf,.doc,.docx"></div>
                    </div>
                    <div class="form-group"><label>Cover Letter</label><textarea name="cover_letter" placeholder="Why do you want to join us?"></textarea></div>
                    <button type="submit" class="submit-btn">Submit Application</button>
                </form>
            </div>
        </div>
        <?php endforeach; else: ?>
        <div class="empty-state"><i class="fas fa-briefcase"></i><p>No open positions at the moment. Check back soon!</p></div>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
