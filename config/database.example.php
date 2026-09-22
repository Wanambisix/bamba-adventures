<?php
// ================================================================
// BAMBA ADVENTURES - Database Configuration          (TEMPLATE)
// ================================================================
//
// SETUP: copy this file to  config/database.php  and fill in the real
// values.  config/database.php is listed in .gitignore on purpose - it
// holds live credentials and must never be committed.
//
// On cPanel the database name AND user are prefixed with your account
// username, e.g.  account_bamba_db  /  account_bamba_user
//
// There is no $API_KEY here: the original file defined one, but nothing
// in the codebase ever used it, so it has been dropped rather than
// carried forward as an unused secret.

$DB_HOST = 'localhost';
$DB_NAME = 'CHANGE_ME_database_name';
$DB_USER = 'CHANGE_ME_database_user';
$DB_PASS = 'CHANGE_ME_database_password';

try {
    $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch (PDOException $e) {
    // Don't die - let pages show fallback content when DB is offline
    $pdo = null;
}
