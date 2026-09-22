<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$faq = null;
$errors = [];

if (!empty($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM faqs WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $faq = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $data = [
        'question' => trim($_POST['question'] ?? ''),
        'answer' => trim($_POST['answer'] ?? ''),
        'category' => trim($_POST['category'] ?? ''),
        'status' => $_POST['status'] ?? 'active',
        'sort_order' => intval($_POST['sort_order'] ?? 0),
    ];
    
    if (empty($data['question']) || empty($data['answer'])) $errors[] = 'Question and answer are required';
    
    if (empty($errors)) {
        if ($faq) {
            $sql = "UPDATE faqs SET question=:question, answer=:answer, category=:category, status=:status, sort_order=:sort_order WHERE id=:id";
            $data['id'] = $faq['id'];
            $stmt = $pdo->prepare($sql);
            $stmt->execute($data);
        } else {
            $sql = "INSERT INTO faqs (question, answer, category, status, sort_order) VALUES (:question, :answer, :category, :status, :sort_order)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($data);
        }
        header('Location: /admin/faq.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $faq ? 'Edit' : 'Add'; ?> FAQ | Bamba Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main">
        <div class="topbar">
            <h1><?php echo $faq ? 'Edit' : 'Add'; ?> FAQ</h1>
            <a href="faq.php" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Back</a>
        </div>
        <div class="content">
            <?php if ($errors): ?>
            <div class="alert alert-error"><?php echo implode('<br>', $errors); ?></div>
            <?php endif; ?>
            <form method="POST">
            <?php echo csrf_field(); ?>
                <div class="card">
                    <div class="card-header"><h2>FAQ Details</h2></div>
                    <div class="card-body">
                        <div class="form-grid">
                            <div class="form-group full">
                                <label>Question *</label>
                                <input type="text" name="question" value="<?php echo esc($faq['question'] ?? ''); ?>" required>
                            </div>
                            <div class="form-group full">
                                <label>Answer *</label>
                                <textarea name="answer" rows="4" required><?php echo esc($faq['answer'] ?? ''); ?></textarea>
                            </div>
                            <div class="form-group">
                                <label>Category</label>
                                <input type="text" name="category" value="<?php echo esc($faq['category'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label>Status</label>
                                <select name="status">
                                    <option value="active" <?php echo ($faq['status'] ?? '') === 'active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="hidden" <?php echo ($faq['status'] ?? '') === 'hidden' ? 'selected' : ''; ?>>Hidden</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Sort Order</label>
                                <input type="number" name="sort_order" value="<?php echo esc($faq['sort_order'] ?? 0); ?>">
                            </div>
                        </div>
                    </div>
                </div>
                <div style="display:flex;gap:1rem;margin-top:1rem;">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save FAQ</button>
                    <a href="faq.php" class="btn btn-outline">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
