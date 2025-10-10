<?php
// config.php - koneksi db
session_start();
$DB_HOST = '127.0.0.1';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'kasir';

try {
    $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    die('Koneksi gagal: ' . $e->getMessage());
}
function is_logged_in() {
    return isset($_SESSION['user']);
}
function require_login() {
    if (!is_logged_in()) {
        header('Location: index.php');
        exit;
    }
}
function require_role($role) {
    require_login();
    if ($_SESSION['user']['role'] !== $role) {
        die('Akses ditolak.');
    }
}
?>