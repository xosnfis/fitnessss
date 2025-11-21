<?php
// Основная конфигурация приложения
session_start();

// Настройки безопасности
define('SITE_URL', 'http://localhost/fitness-center');
define('ADMIN_EMAIL', 'admin@fitness.ru');

// Подключение к базе данных
require_once __DIR__ . '/database.php';

// Функции безопасности
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: index.php');
        exit;
    }
}

function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function redirect($url) {
    header("Location: $url");
    exit;
}
?>

