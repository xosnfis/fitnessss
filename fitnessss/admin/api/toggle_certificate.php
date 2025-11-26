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

// Получаем данные
$certificate_id = isset($_POST['certificate_id']) ? (int)$_POST['certificate_id'] : 0;
$is_active = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 0;

if (empty($certificate_id)) {
    echo json_encode(['success' => false, 'message' => 'Не указан ID сертификата']);
    exit;
}

try {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("UPDATE certificates SET is_active = ? WHERE id = ?");
    $stmt->execute([$is_active, $certificate_id]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Статус сертификата обновлен'
    ]);
    
} catch (PDOException $e) {
    error_log("Toggle certificate error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Ошибка при обновлении статуса'
    ]);
}

