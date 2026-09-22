<?php
require_once __DIR__ . '/../includes/functions.php';
$slug = $_GET['slug'] ?? '';
$post = getBlogBySlug($slug);

if (!$post) {
    http_response_code(404);
    $pageTitle = 'Post Not Found';
    include __DIR__ . '/../includes/header.php';
    echo '<div class="page-hero"><h1>Post Not Found</h1><p>The blog post you are looking for does not exist.</p></div>';
    include __DIR__ . '/../includes/footer.php';
    exit;
}

$pageTitle       = ($post['meta_title'] ?: $post['title']) . ' | Bamba Adventures';
$pageDescription = $post['meta_description'] ?: excerpt($post['excerpt'] ?? ($post['content'] ?? ''), 155);
$pageOgImage     = $post['image'] ?? '';
$canonicalUrl    = '/blog/post/' . $post['slug'];
$ogType          = 'article';
$seoBlogPost     = $post;
$breadcrumbs     = [
    ['name' => 'Home', 'url' => '/'],
    ['name' => 'Blog', 'url' => '/blog'],
    ['name' => $post['title'], 'url' => '/blog/post/' . $post['slug']],
];

$related = getBlogPosts(3);
$related = array_filter($related, fn($p) => $p['id'] != $post['id']);
$related = array_slice($related, 0, 3);

include __DIR__ . '/../includes/header.php';
?>

<style>
.blog-hero { height: 450px; background-size: cover; background-position: center; position: relative; }
.blog-hero-overlay { position: absolute; inset: 0; background: linear-gradient(transparent 20%, rgba(0,0,0,0.7)); }
.blog-hero-content { position: absolute; bottom: 0; left: 0; width: 100%; padding: 3rem 5%; color: var(--white); max-width: 1200px; margin: 0 auto; }
.blog-hero-content h1 { font-family: var(--font-serif); font-size: clamp(1.6rem, 4vw, 2.6rem); margin-bottom: 0.5rem; }
.blog-body { max-width: 780px; margin: 0 auto; padding: 4rem 5%; color: var(--text-light); line-height: 1.8; font-size: 1rem; overflow-wrap: break-word; word-break: break-word; }
.blog-body h2 { font-family: var(--font-serif); color: var(--primary-dark); margin: 2rem 0 1rem; font-size: 1.6rem; }
.blog-body h3 { font-family: var(--font-serif); color: var(--primary-dark); margin: 1.75rem 0 0.75rem; font-size: 1.3rem; }
.blog-body h4 { color: var(--text-dark); margin: 1.5rem 0 0.5rem; font-size: 1.1rem; }
.blog-body p { margin-bottom: 1.2rem; color: var(--text-light); }
.blog-body ul, .blog-body ol { padding-left: 1.6rem; margin: 0.75rem 0 1.25rem; }
.blog-body ul { list-style: disc; }
.blog-body ol { list-style: decimal; }
.blog-body li { margin-bottom: 0.5rem; color: var(--text-light); line-height: 1.8; }
.blog-body strong { color: var(--text-dark); font-weight: 700; }
.blog-body em { font-style: italic; }
.blog-body a { color: var(--primary-dark); text-decoration: underline; }
.blog-body img { max-width: 100%; border-radius: 12px; margin: 1.5rem 0; display: block; }
.blog-body blockquote { border-left: 4px solid var(--primary); padding: 0.75rem 1.25rem; font-style: italic; color: var(--text); margin: 1.5rem 0; background: #faf8f6; border-radius: 0 8px 8px 0; }
</style>

<div class="blog-hero" style="background-image:url('<?php echo esc($post['image'] ?: '/assets/images/blog-placeholder.jpg'); ?>');">
    <div class="blog-hero-overlay"></div>
    <div class="blog-hero-content">
        <p style="font-size:0.8rem; opacity:0.75; margin-bottom:0.5rem;"><?php echo formatDate($post['published_at']); ?> &middot; <?php echo esc($post['category'] ?? 'Travel'); ?></p>
        <h1><?php echo esc($post['title']); ?></h1>
    </div>
</div>

<div class="breadcrumb" style="padding-top:1rem;">
    <div class="container">
        <ol>
            <li><a href="/">Home</a></li>
            <li><a href="/blog">Journal</a></li>
            <li><?php echo esc(truncate($post['title'], 30)); ?></li>
        </ol>
    </div>
</div>

<div class="blog-body">
    <?php
    $content = $post['content'] ?? '';
    if ($content) {
        // If content contains HTML tags (saved via Quill), output as-is.
        // If plain text (old posts), convert newlines to paragraphs.
        if (strip_tags($content) === $content) {
            // Plain text — wrap each double-newline block in <p>
            $paras = preg_split('/\n{2,}/', trim($content));
            foreach ($paras as $para) {
                $para = trim($para);
                if ($para !== '') echo '<p>' . nl2br(esc($para)) . '</p>';
            }
        } else {
            echo $content;
        }
    }
    ?>
</div>

<?php if ($related): ?>
<section class="section section-light">
    <div class="container">
        <div class="section-header"><h2>More Stories</h2></div>
        <div class="card-grid">
            <?php foreach ($related as $bp): ?>
            <article class="card">
                <a href="/blog/post/<?php echo esc($bp['slug']); ?>">
                    <div class="card-image" style="background-image:url('<?php echo esc($bp['image'] ?: '/assets/images/blog-placeholder.jpg'); ?>'); height:200px;"></div>
                    <div class="card-body">
                        <h3><?php echo esc($bp['title']); ?></h3>
                        <p><?php echo esc(truncate(strip_tags($bp['excerpt'] ?? $bp['content'] ?? ''), 70)); ?></p>
                    </div>
                </a>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
