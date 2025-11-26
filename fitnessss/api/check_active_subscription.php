<?php
header('Content-Type: application/json');
require_once '../config/config.php';

// Проверка авторизации
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'has_active' => false, 'message' => 'Необходима авторизация']);
    exit;
}

try {
    $pdo = getDBConnection();
    
    // Проверяем наличие активного абонемента у пользователя
    // Активный абонемент: is_active = TRUE и end_date >= текущая дата
    $stmt = $pdo->prepare("SELECT us.*, s.name as subscription_name 
                           FROM user_subscriptions us
                           JOIN subscriptions s ON us.subscription_id = s.id
                           WHERE us.user_id = ? 
                           AND us.is_active = TRUE 
                           AND us.end_date >= CURDATE()
                           LIMIT 1");
    $stmt->execute([$_SESSION['user_id']]);
    $active_subscription = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($active_subscription) {
        echo json_encode([
            'success' => true,
            'has_active' => true,
            'subscription' => [
                'name' => $active_subscription['subscription_name'],
                'end_date' => $active_subscription['end_date']
            ],
            'message' => 'У вас уже есть активный абонемент'
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'has_active' => false,
            'message' => 'Активного абонемента нет'
        ]);
    }
    
} catch (PDOException $e) {
    error_log("Check active subscription error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'has_active' => false,
        'message' => 'Ошибка при проверке абонемента'
    ]);
}

