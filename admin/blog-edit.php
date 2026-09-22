<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$post = null;
$errors = [];

if (!empty($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $post = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $data = [
        'title' => trim($_POST['title'] ?? ''),
        'slug' => slugify(trim($_POST['slug'] ?? $_POST['title'] ?? '')),
        'excerpt' => trim($_POST['excerpt'] ?? ''),
        'content' => trim($_POST['content'] ?? ''),
        'image' => trim($_POST['image'] ?? ''),
        'author' => trim($_POST['author'] ?? ''),
        'category' => trim($_POST['category'] ?? ''),
        'status' => $_POST['status'] ?? 'draft',
        'meta_title' => trim($_POST['meta_title'] ?? ''),
        'meta_description' => trim($_POST['meta_description'] ?? ''),
        'published_at' => $_POST['published_at'] ?: null,
    ];
    
    // Handle image upload
    if (!empty($_FILES['image_file']['tmp_name']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
        $result = uploadImage($_FILES['image_file'], 'blog');
        if (!empty($result['success'])) $data['image'] = '/' . $result['path'];
    } elseif (empty($data['image'])) {
        $data['image'] = $post['image'] ?? '';
    }

    if (empty($data['title'])) $errors[] = 'Title is required';

    if (empty($errors)) {
        if ($post) {
            $sql = "UPDATE blog_posts SET title=:title, slug=:slug, excerpt=:excerpt, content=:content, image=:image, author=:author, category=:category, status=:status, meta_title=:meta_title, meta_description=:meta_description, published_at=:published_at WHERE id=:id";
            $data['id'] = $post['id'];
            $stmt = $pdo->prepare($sql);
            $stmt->execute($data);
        } else {
            $sql = "INSERT INTO blog_posts (title, slug, excerpt, content, image, author, category, status, meta_title, meta_description, published_at) VALUES (:title, :slug, :excerpt, :content, :image, :author, :category, :status, :meta_title, :meta_description, :published_at)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($data);
        }
        header('Location: /admin/blog.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $post ? 'Edit' : 'Add'; ?> Post | Bamba Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
    <link href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css" rel="stylesheet">
    <style>
        .quill-field { border: 1.5px solid var(--border); border-radius: 8px; overflow: hidden; margin-top: 0.25rem; }
        .quill-field:focus-within { border-color: var(--primary); }
        .quill-field .ql-toolbar { background: #fafafa; }
        .quill-field .ql-editor { min-height: 320px; font-family: 'Inter', sans-serif; font-size: 0.92rem; line-height: 1.8; color: #333; }
        .slug-wrap { position: relative; }
        .slug-wrap input { padding-right: 2.2rem; }
        .slug-sync-icon { position: absolute; right: 0.7rem; top: 50%; transform: translateY(-50%); color: var(--primary); font-size: 0.8rem; pointer-events: none; transition: opacity 0.2s; }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main">
        <div class="topbar">
            <h1><?php echo $post ? 'Edit' : 'Add'; ?> Post</h1>
            <a href="blog.php" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Back</a>
        </div>
        <div class="content">
            <?php if ($errors): ?>
            <div class="alert alert-error"><?php echo implode('<br>', $errors); ?></div>
            <?php endif; ?>
            <form method="POST" enctype="multipart/form-data">
            <?php echo csrf_field(); ?>
                <div class="card">
                    <div class="card-header"><h2>Post Details</h2></div>
                    <div class="card-body">
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Title *</label>
                                <input type="text" name="title" id="blogTitle" value="<?php echo esc($post['title'] ?? ''); ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Slug <small style="color:var(--text-light);font-weight:400;">(auto-filled from title)</small></label>
                                <div class="slug-wrap">
                                    <input type="text" name="slug" id="blogSlug" value="<?php echo esc($post['slug'] ?? ''); ?>" placeholder="auto-generated from title">
                                    <i class="fas fa-link slug-sync-icon" id="blogSlugIcon" title="Auto-syncing from title"></i>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Author</label>
                                <input type="text" name="author" value="<?php echo esc($post['author'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label>Category</label>
                                <input type="text" name="category" value="<?php echo esc($post['category'] ?? ''); ?>" placeholder="e.g. Travel Tips">
                            </div>
                            <div class="form-group">
                                <label>Status</label>
                                <select name="status">
                                    <option value="published" <?php echo ($post['status'] ?? '') === 'published' ? 'selected' : ''; ?>>Published</option>
                                    <option value="draft" <?php echo ($post['status'] ?? '') === 'draft' ? 'selected' : ''; ?>>Draft</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Published Date</label>
                                <input type="date" name="published_at" value="<?php echo esc($post['published_at'] ?? ''); ?>">
                            </div>
                            <div class="form-group full">
                                <label>Featured Image</label>
                                <input type="file" name="image_file" accept="image/*">
                                <?php if (!empty($post['image'])): ?>
                                <div style="margin-top:0.5rem;"><img src="<?php echo esc($post['image']); ?>" alt="Current" style="max-height:100px;border-radius:6px;"></div>
                                <?php endif; ?>
                                <label style="margin-top:0.75rem;font-size:0.85rem;color:#888;">Or paste URL</label>
                                <input type="text" name="image" value="<?php echo esc($post['image'] ?? ''); ?>" placeholder="https://...">
                            </div>
                            <div class="form-group full">
                                <label>Excerpt</label>
                                <textarea name="excerpt" rows="2"><?php echo esc($post['excerpt'] ?? ''); ?></textarea>
                            </div>
                            <div class="form-group full" style="margin-bottom:0">
                                <label>Content</label>
                                <div id="blogEditor" class="quill-field"></div>
                                <textarea name="content" id="blogContent" style="display:none;"><?php echo esc($post['content'] ?? ''); ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <details class="seo-details" <?php echo (!empty($post['meta_title']) || !empty($post['meta_description'])) ? 'open' : ''; ?>>
                    <summary class="seo-summary">
                        <i class="fas fa-search"></i> Advanced SEO
                        <span class="seo-note">— optional, auto-generated from post title &amp; excerpt if left empty</span>
                        <i class="fas fa-chevron-down seo-chevron"></i>
                    </summary>
                    <div class="seo-body">
                        <div class="form-group">
                            <label>Meta Title <small style="color:var(--text-light);font-weight:400;">— overrides auto-generated title in search results</small></label>
                            <input type="text" name="meta_title" value="<?php echo esc($post['meta_title'] ?? ''); ?>" placeholder="Leave empty to auto-generate: <?php echo esc($post['title'] ?? 'Post Title'); ?> | Bamba Adventures">
                        </div>
                        <div class="form-group">
                            <label>Meta Description <small style="color:var(--text-light);font-weight:400;">— overrides auto-generated snippet in search results</small></label>
                            <textarea name="meta_description" rows="2" placeholder="Leave empty to auto-generate from excerpt (first 155 characters)"><?php echo esc($post['meta_description'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </details>
                <div style="display:flex;gap:1rem;margin-top:1rem;">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Post</button>
                    <a href="blog.php" class="btn btn-outline">Cancel</a>
                </div>
            </form>
        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>
<script>
/* ── Slug auto-sync from title ── */
(function() {
    var titleEl    = document.getElementById('blogTitle');
    var slugEl     = document.getElementById('blogSlug');
    var iconEl     = document.getElementById('blogSlugIcon');
    var slugManual = slugEl.value !== '';

    function toSlug(s) {
        return s.toLowerCase()
                .replace(/[^a-z0-9\s-]/g, '')
                .trim()
                .replace(/[\s]+/g, '-')
                .replace(/-+/g, '-');
    }

    function syncSlug() {
        if (slugManual) return;
        slugEl.value = toSlug(titleEl.value);
    }

    titleEl.addEventListener('input', syncSlug);

    slugEl.addEventListener('input', function() {
        slugManual = slugEl.value !== '';
        if (iconEl) iconEl.style.opacity = slugManual ? '0.3' : '1';
    });

    // Initialise icon state
    if (iconEl) iconEl.style.opacity = slugManual ? '0.3' : '1';
})();

/* ── Quill rich-text editor ── */
(function() {
    var ta = document.getElementById('blogContent');
    var quill = new Quill('#blogEditor', {
        theme: 'snow',
        modules: {
            toolbar: [
                [{ 'header': [2, 3, 4, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                [{ 'indent': '-1' }, { 'indent': '+1' }],
                ['link', 'image', 'blockquote', 'clean']
            ]
        }
    });

    if (ta.value.trim()) {
        quill.root.innerHTML = ta.value;
    }

    document.querySelector('form').addEventListener('submit', function() {
        var html = quill.root.innerHTML;
        ta.value = (html === '<p><br></p>') ? '' : html;
    });
})();

</script>
</body>
</html>
