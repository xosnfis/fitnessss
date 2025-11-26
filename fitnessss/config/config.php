<?php
// Основная конфигурация приложения
session_start();

// Автоматическое определение окружения (локальное или хостинг)
$is_local = (
    $_SERVER['HTTP_HOST'] === 'localhost' || 
    $_SERVER['HTTP_HOST'] === '127.0.0.1' ||
    strpos($_SERVER['HTTP_HOST'], 'localhost') !== false ||
    strpos($_SERVER['HTTP_HOST'], '.local') !== false
);

// Настройки URL сайта
if ($is_local) {
    // Локальная разработка
    define('SITE_URL', 'http://localhost/fitnessss');
} else {
    // Продакшн (автоматически определяется)
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $path = dirname($_SERVER['SCRIPT_NAME']);
    define('SITE_URL', $protocol . '://' . $host . ($path !== '/' ? $path : ''));
}

// Настройки безопасности
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
