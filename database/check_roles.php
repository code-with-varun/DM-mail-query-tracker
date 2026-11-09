<?php
require_once __DIR__ . '/../app/config/database.php';
$pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET, DB_USER, DB_PASS, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);

echo "=== ROLES TABLE ===\n";
$roles = $pdo->query("SELECT * FROM roles ORDER BY role_id ASC")->fetchAll();
foreach ($roles as $r) {
    echo "ID: {$r['role_id']} | Name: {$r['role_name']} | Desc: {$r['description']}\n";
}

echo "\n=== USERS TABLE ===\n";
$users = $pdo->query("SELECT u.id, u.user_code, u.full_name, u.role_id, r.role_name FROM users u LEFT JOIN roles r ON u.role_id = r.role_id ORDER BY u.id ASC")->fetchAll();
foreach ($users as $u) {
    echo "ID: {$u['id']} | Code: {$u['user_code']} | Name: {$u['full_name']} | Role ID: {$u['role_id']} | Role Name: {$u['role_name']}\n";
}
