<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$career = null;
$errors = [];

if (!empty($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM careers WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $career = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $data = [
        'title' => trim($_POST['title'] ?? ''),
        'department' => trim($_POST['department'] ?? ''),
        'location' => trim($_POST['location'] ?? ''),
        'job_type' => $_POST['job_type'] ?? 'full-time',
        'description' => trim($_POST['description'] ?? ''),
        'requirements' => trim($_POST['requirements'] ?? ''),
        'status' => $_POST['status'] ?? 'active',
    ];
    
    if (empty($data['title'])) $errors[] = 'Title is required';
    
    if (empty($errors)) {
        if ($career) {
            $sql = "UPDATE careers SET title=:title, department=:department, location=:location, job_type=:job_type, description=:description, requirements=:requirements, status=:status WHERE id=:id";
            $data['id'] = $career['id'];
            $stmt = $pdo->prepare($sql);
            $stmt->execute($data);
        } else {
            $sql = "INSERT INTO careers (title, department, location, job_type, description, requirements, status) VALUES (:title, :department, :location, :job_type, :description, :requirements, :status)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($data);
        }
        header('Location: /admin/careers.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $career ? 'Edit' : 'Add'; ?> Job | Bamba Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main">
        <div class="topbar">
            <h1><?php echo $career ? 'Edit' : 'Add'; ?> Job</h1>
            <a href="careers.php" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Back</a>
        </div>
        <div class="content">
            <?php if ($errors): ?>
            <div class="alert alert-error"><?php echo implode('<br>', $errors); ?></div>
            <?php endif; ?>
            <form method="POST">
            <?php echo csrf_field(); ?>
                <div class="card">
                    <div class="card-header"><h2>Job Details</h2></div>
                    <div class="card-body">
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Title *</label>
                                <input type="text" name="title" value="<?php echo esc($career['title'] ?? ''); ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Department</label>
                                <input type="text" name="department" value="<?php echo esc($career['department'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label>Location</label>
                                <input type="text" name="location" value="<?php echo esc($career['location'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label>Job Type</label>
                                <select name="job_type">
                                    <option value="full-time" <?php echo ($career['job_type'] ?? '') === 'full-time' ? 'selected' : ''; ?>>Full-time</option>
                                    <option value="part-time" <?php echo ($career['job_type'] ?? '') === 'part-time' ? 'selected' : ''; ?>>Part-time</option>
                                    <option value="contract" <?php echo ($career['job_type'] ?? '') === 'contract' ? 'selected' : ''; ?>>Contract</option>
                                    <option value="internship" <?php echo ($career['job_type'] ?? '') === 'internship' ? 'selected' : ''; ?>>Internship</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Status</label>
                                <select name="status">
                                    <option value="active" <?php echo ($career['status'] ?? '') === 'active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="closed" <?php echo ($career['status'] ?? '') === 'closed' ? 'selected' : ''; ?>>Closed</option>
                                </select>
                            </div>
                            <div class="form-group full">
                                <label>Description</label>
                                <textarea name="description" rows="4"><?php echo esc($career['description'] ?? ''); ?></textarea>
                            </div>
                            <div class="form-group full">
                                <label>Requirements</label>
                                <textarea name="requirements" rows="4"><?php echo esc($career['requirements'] ?? ''); ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div style="display:flex;gap:1rem;margin-top:1rem;">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Job</button>
                    <a href="careers.php" class="btn btn-outline">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
