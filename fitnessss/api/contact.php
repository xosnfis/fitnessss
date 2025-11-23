<?php
header('Content-Type: application/json');
require_once '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Метод не разрешен']);
    exit;
}

$name = sanitize($_POST['name'] ?? '');
$email = sanitize($_POST['email'] ?? '');
$phone = sanitize($_POST['phone'] ?? '');
$subject = sanitize($_POST['subject'] ?? '');
$message = sanitize($_POST['message'] ?? '');

// Валидация
if (empty($name) || empty($email) || empty($subject) || empty($message)) {
    echo json_encode(['success' => false, 'message' => 'Все обязательные поля должны быть заполнены']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Некорректный email адрес']);
    exit;
}

// Здесь можно добавить сохранение в базу данных или отправку email
// Для примера просто возвращаем успех

// Логирование обращения
error_log("Contact form: Name: $name, Email: $email, Phone: $phone, Subject: $subject, Message: $message");

echo json_encode([
    'success' => true, 
    'message' => 'Спасибо! Ваше сообщение отправлено. Мы свяжемся с вами в ближайшее время.'
]);

