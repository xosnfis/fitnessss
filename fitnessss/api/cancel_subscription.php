<?php
header('Content-Type: application/json');
require_once '../config/config.php';

// Проверка авторизации
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Необходима авторизация']);
    exit;
}

// Проверка метода запроса
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Метод не разрешен']);
    exit;
}

// Получаем ID подписки для отмены
$subscription_id = isset($_POST['subscription_id']) ? (int)$_POST['subscription_id'] : 0;

if (empty($subscription_id)) {
    echo json_encode(['success' => false, 'message' => 'Не указан ID абонемента']);
    exit;
}

try {
    $pdo = getDBConnection();
    $pdo->beginTransaction();
    
    // Проверяем, что абонемент принадлежит пользователю и активен
    $stmt = $pdo->prepare("SELECT us.*, s.name as subscription_name 
                           FROM user_subscriptions us
                           JOIN subscriptions s ON us.subscription_id = s.id
                           WHERE us.id = ? 
                           AND us.user_id = ? 
                           AND us.is_active = TRUE 
                           AND us.end_date >= CURDATE()");
    $stmt->execute([$subscription_id, $_SESSION['user_id']]);
    $subscription = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$subscription) {
        $pdo->rollBack();
        echo json_encode([
            'success' => false, 
            'message' => 'Абонемент не найден или уже неактивен'
        ]);
        exit;
    }
    
    // Деактивируем абонемент
    $stmt = $pdo->prepare("UPDATE user_subscriptions 
                           SET is_active = FALSE 
                           WHERE id = ? AND user_id = ?");
    $result = $stmt->execute([$subscription_id, $_SESSION['user_id']]);
    
    if (!$result) {
        $pdo->rollBack();
        echo json_encode([
            'success' => false, 
            'message' => 'Ошибка при отмене абонемента'
        ]);
        exit;
    }
    
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Абонемент успешно отменен',
        'subscription_name' => $subscription['subscription_name']
    ]);
    
} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Cancel subscription error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Ошибка при отмене абонемента. Попробуйте позже.'
    ]);
}

