<?php
require_once '../config/config.php';
requireLogin();

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID заказа не указан']);
    exit;
}

$order_id = (int)$_GET['id'];

try {
    $pdo = getDBConnection();
    
    // Проверка прав доступа
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
    $stmt->execute([$order_id, $_SESSION['user_id']]);
    $order = $stmt->fetch();
    
    if (!$order && !isAdmin()) {
        echo json_encode(['success' => false, 'message' => 'Заказ не найден']);
        exit;
    }
    
    // Получение элементов заказа
    $stmt = $pdo->prepare("SELECT oi.*, 
                          CASE 
                              WHEN oi.item_type = 'service' THEN s.name
                              WHEN oi.item_type = 'subscription' THEN sub.name
                          END as name
                          FROM order_items oi
                          LEFT JOIN services s ON oi.item_type = 'service' AND oi.item_id = s.id
                          LEFT JOIN subscriptions sub ON oi.item_type = 'subscription' AND oi.item_id = sub.id
                          WHERE oi.order_id = ?");
    $stmt->execute([$order_id]);
    $items = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'items' => $items,
        'total' => $order['total_amount']
    ]);
} catch (PDOException $e) {
    error_log("Get order details error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Ошибка при получении данных']);
}

