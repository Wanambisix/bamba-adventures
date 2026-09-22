<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$testimonial = null;
$errors = [];
$tours = getTours(['status' => 'active']);

if (!empty($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM testimonials WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $testimonial = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $data = [
        'name' => trim($_POST['name'] ?? ''),
        'role' => trim($_POST['role'] ?? ''),
        'text' => trim($_POST['text'] ?? ''),
        'avatar' => trim($_POST['avatar'] ?? ''),
        'rating' => intval($_POST['rating'] ?? 5),
        'tour_id' => $_POST['tour_id'] ?: null,
        'status' => $_POST['status'] ?? 'active',
        'sort_order' => intval($_POST['sort_order'] ?? 0),
    ];
    
    // Handle avatar upload
    if (!empty($_FILES['avatar_file']['tmp_name']) && $_FILES['avatar_file']['error'] === UPLOAD_ERR_OK) {
        $result = uploadImage($_FILES['avatar_file'], 'testimonials');
        if (!empty($result['success'])) $data['avatar'] = '/' . $result['path'];
    } elseif (empty($data['avatar'])) {
        $data['avatar'] = $testimonial['avatar'] ?? '';
    }

    if (empty($data['name']) || empty($data['text'])) $errors[] = 'Name and text are required';

    if (empty($errors)) {
        if ($testimonial) {
            $sql = "UPDATE testimonials SET name=:name, role=:role, text=:text, avatar=:avatar, rating=:rating, tour_id=:tour_id, status=:status, sort_order=:sort_order WHERE id=:id";
            $data['id'] = $testimonial['id'];
            $stmt = $pdo->prepare($sql);
            $stmt->execute($data);
        } else {
            $sql = "INSERT INTO testimonials (name, role, text, avatar, rating, tour_id, status, sort_order) VALUES (:name, :role, :text, :avatar, :rating, :tour_id, :status, :sort_order)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($data);
        }
        header('Location: /admin/testimonials.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $testimonial ? 'Edit' : 'Add'; ?> Testimonial | Bamba Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main">
        <div class="topbar">
            <h1><?php echo $testimonial ? 'Edit' : 'Add'; ?> Testimonial</h1>
            <a href="testimonials.php" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Back</a>
        </div>
        <div class="content">
            <?php if ($errors): ?>
            <div class="alert alert-error"><?php echo implode('<br>', $errors); ?></div>
            <?php endif; ?>
            <form method="POST" enctype="multipart/form-data">
            <?php echo csrf_field(); ?>
                <div class="card">
                    <div class="card-header"><h2>Testimonial Details</h2></div>
                    <div class="card-body">
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Name *</label>
                                <input type="text" name="name" value="<?php echo esc($testimonial['name'] ?? ''); ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Role / Title</label>
                                <input type="text" name="role" value="<?php echo esc($testimonial['role'] ?? ''); ?>" placeholder="e.g. Traveler from USA">
                            </div>
                            <div class="form-group">
                                <label>Rating</label>
                                <select name="rating">
                                    <?php for ($i = 5; $i >= 1; $i--): ?>
                                    <option value="<?php echo $i; ?>" <?php echo ($testimonial['rating'] ?? 5) == $i ? 'selected' : ''; ?>><?php echo $i; ?> Stars</option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Tour (optional)</label>
                                <select name="tour_id">
                                    <option value="">-- Select --</option>
                                    <?php foreach ($tours as $t): ?>
                                    <option value="<?php echo $t['id']; ?>" <?php echo ($testimonial['tour_id'] ?? '') == $t['id'] ? 'selected' : ''; ?>><?php echo esc($t['title']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Status</label>
                                <select name="status">
                                    <option value="active" <?php echo ($testimonial['status'] ?? '') === 'active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="hidden" <?php echo ($testimonial['status'] ?? '') === 'hidden' ? 'selected' : ''; ?>>Hidden</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Sort Order</label>
                                <input type="number" name="sort_order" value="<?php echo esc($testimonial['sort_order'] ?? 0); ?>">
                            </div>
                            <div class="form-group">
                                <label>Avatar Photo</label>
                                <input type="file" name="avatar_file" accept="image/*">
                                <?php if (!empty($testimonial['avatar'])): ?>
                                <div style="margin-top:0.5rem;"><img src="<?php echo esc($testimonial['avatar']); ?>" alt="Current" style="max-height:80px;border-radius:50%;aspect-ratio:1;object-fit:cover;"></div>
                                <?php endif; ?>
                                <label style="margin-top:0.75rem;font-size:0.85rem;color:#888;">Or paste URL</label>
                                <input type="text" name="avatar" value="<?php echo esc($testimonial['avatar'] ?? ''); ?>" placeholder="https://...">
                            </div>
                            <div class="form-group full">
                                <label>Testimonial Text *</label>
                                <textarea name="text" rows="4" required><?php echo esc($testimonial['text'] ?? ''); ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div style="display:flex;gap:1rem;margin-top:1rem;">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Testimonial</button>
                    <a href="testimonials.php" class="btn btn-outline">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
