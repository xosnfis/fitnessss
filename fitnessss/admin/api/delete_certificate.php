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

// Получаем ID сертификата
$certificate_id = isset($_POST['certificate_id']) ? (int)$_POST['certificate_id'] : 0;

if (empty($certificate_id)) {
    echo json_encode(['success' => false, 'message' => 'Не указан ID сертификата']);
    exit;
}

try {
    $pdo = getDBConnection();
    $pdo->beginTransaction();
    
    // Получаем информацию о сертификате
    $stmt = $pdo->prepare("SELECT image_path FROM certificates WHERE id = ?");
    $stmt->execute([$certificate_id]);
    $certificate = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$certificate) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Сертификат не найден']);
        exit;
    }
    
    // Удаляем запись из БД
    $stmt = $pdo->prepare("DELETE FROM certificates WHERE id = ?");
    $stmt->execute([$certificate_id]);
    
    // Удаляем файл
    $file_path = '../../' . $certificate['image_path'];
    if (file_exists($file_path)) {
        unlink($file_path);
    }
    
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Сертификат успешно удален'
    ]);
    
} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Delete certificate error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Ошибка при удалении сертификата'
    ]);
}

