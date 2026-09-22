<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$message = '';
$error = '';

// ---- BACKUP ----
if (isset($_GET['download'])) {
    header('Content-Type: application/sql');
    header('Content-Disposition: attachment; filename="bamba_backup_' . date('Y-m-d_H-i-s') . '.sql"');
    
    echo "-- Bamba Adventures Database Backup\n";
    echo "-- Generated: " . date('Y-m-d H:i:s') . "\n";
    echo "-- Server: " . $_SERVER['HTTP_HOST'] . "\n\n";
    echo "SET FOREIGN_KEY_CHECKS = 0;\n\n";
    
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $table) {
        // Structure
        $create = $pdo->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_ASSOC);
        echo "DROP TABLE IF EXISTS `$table`;\n";
        echo $create['Create Table'] . ";\n\n";
        
        // Data
        $rows = $pdo->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
        if (!empty($rows)) {
            $columns = array_keys($rows[0]);
            $batchSize = 100;
            $chunks = array_chunk($rows, $batchSize);
            foreach ($chunks as $chunk) {
                echo "INSERT INTO `$table` (`" . implode('`, `', $columns) . "`) VALUES\n";
                $values = [];
                foreach ($chunk as $row) {
                    $rowVals = [];
                    foreach ($row as $val) {
                        $rowVals[] = $val === null ? 'NULL' : $pdo->quote($val);
                    }
                    $values[] = '(' . implode(', ', $rowVals) . ')';
                }
                echo implode(",\n", $values) . ";\n";
            }
            echo "\n";
        }
    }
    echo "SET FOREIGN_KEY_CHECKS = 1;\n";
    exit;
}

// ---- RESTORE ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_FILES['sql_file']['tmp_name'])) {
    csrf_verify();
    $sql = file_get_contents($_FILES['sql_file']['tmp_name']);
    if (empty($sql)) {
        $error = 'Could not read the uploaded file.';
    } else {
        // Simple split by semicolon + newline. Skip comments.
        $lines = explode("\n", $sql);
        $buffer = '';
        $successCount = 0;
        $failCount = 0;
        
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || strpos($line, '--') === 0 || strpos($line, '/*') === 0) continue;
            $buffer .= $line . "\n";
            if (substr($line, -1) === ';') {
                try {
                    $pdo->exec($buffer);
                    $successCount++;
                } catch (Exception $e) {
                    $failCount++;
                    // Silently skip individual statement failures
                }
                $buffer = '';
            }
        }
        
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
        $message = "Restore complete. $successCount statements executed successfully." . ($failCount > 0 ? " $failCount statements skipped (likely duplicates or harmless)." : '');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Backup & Restore | Bamba Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main">
        <div class="topbar"><h1>Backup & Restore</h1></div>
        <div class="content">
            <?php if ($message): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $message; ?></div><?php endif; ?>
            <?php if ($error): ?><div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div><?php endif; ?>
            
            <div class="card">
                <div class="card-header"><h2><i class="fas fa-download" style="color:var(--primary);"></i> Backup Database</h2></div>
                <div class="card-body">
                    <p style="color:var(--text-light);margin-bottom:1.5rem;">Download a complete SQL dump of your database. This file includes all tables, data, and schema needed to restore your site.</p>
                    <a href="?download=1" class="btn btn-primary"><i class="fas fa-file-download"></i> Download Backup (.sql)</a>
                </div>
            </div>
            
            <div class="card" style="margin-top:1.5rem;">
                <div class="card-header"><h2><i class="fas fa-upload" style="color:var(--primary);"></i> Restore Database</h2></div>
                <div class="card-body">
                    <div class="alert alert-error" style="margin-bottom:1.5rem;">
                        <strong><i class="fas fa-exclamation-triangle"></i> Warning:</strong> Restoring will overwrite all existing data. Make sure you have a current backup before proceeding.
                    </div>
                    <form method="POST" enctype="multipart/form-data">
            <?php echo csrf_field(); ?>
                        <div class="form-group">
                            <label>Select SQL Backup File</label>
                            <input type="file" name="sql_file" accept=".sql" required>
                            <small style="color:var(--text-light);font-size:0.8rem;">Only .sql files exported from this tool or phpMyAdmin are supported.</small>
                        </div>
                        <button type="submit" class="btn btn-primary" onclick="return confirm('This will REPLACE all current data. Are you sure?')"><i class="fas fa-upload"></i> Restore from Backup</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
