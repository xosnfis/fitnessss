<?php
header('Content-Type: application/json');
require_once '../../config/config.php';

// Проверка авторизации
session_start();
if (!isset($_SESSION['user_id']) || !isAdmin()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Необходимы права администратора']);
    exit;
}

// Проверка метода запроса
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Метод не разрешен']);
    exit;
}

// Получаем данные формы
$title = isset($_POST['title']) ? sanitize($_POST['title']) : '';
$description = isset($_POST['description']) ? sanitize($_POST['description']) : '';
$display_order = isset($_POST['display_order']) ? (int)$_POST['display_order'] : 0;

// Валидация
if (empty($title)) {
    echo json_encode(['success' => false, 'message' => 'Название сертификата обязательно']);
    exit;
}

// Проверка загрузки файла
if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Ошибка загрузки файла']);
    exit;
}

$file = $_FILES['image'];
$allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
$max_size = 5 * 1024 * 1024; // 5MB

// Проверка типа файла
if (!in_array($file['type'], $allowed_types)) {
    echo json_encode(['success' => false, 'message' => 'Разрешены только изображения (JPG, PNG, GIF)']);
    exit;
}

// Проверка размера файла
if ($file['size'] > $max_size) {
    echo json_encode(['success' => false, 'message' => 'Размер файла не должен превышать 5MB']);
    exit;
}

// Создаем папку для сертификатов, если её нет
$upload_dir = '../../uploads/certificates/';
if (!file_exists($upload_dir)) {
    if (!mkdir($upload_dir, 0755, true)) {
        echo json_encode(['success' => false, 'message' => 'Ошибка создания папки для загрузки']);
        exit;
    }
}

// Генерируем уникальное имя файла
$file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$file_name = uniqid('cert_', true) . '.' . $file_extension;
$file_path = $upload_dir . $file_name;

// Перемещаем загруженный файл
if (!move_uploaded_file($file['tmp_name'], $file_path)) {
    echo json_encode(['success' => false, 'message' => 'Ошибка сохранения файла']);
    exit;
}

// Сохраняем путь относительно корня сайта
$relative_path = 'uploads/certificates/' . $file_name;

try {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("INSERT INTO certificates (title, description, image_path, display_order, is_active) VALUES (?, ?, ?, ?, TRUE)");
    $stmt->execute([$title, $description, $relative_path, $display_order]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Сертификат успешно загружен'
    ]);
    
} catch (PDOException $e) {
    // Удаляем загруженный файл в случае ошибки БД
    if (file_exists($file_path)) {
        unlink($file_path);
    }
    error_log("Upload certificate error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Ошибка при сохранении в базу данных'
    ]);
}

