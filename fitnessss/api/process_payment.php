<?php
header('Content-Type: application/json');
require_once '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Метод не разрешен']);
    exit;
}

// Проверка авторизации
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Необходима авторизация']);
    exit;
}

// Получаем данные из POST
$order_id = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
$full_name = isset($_POST['full_name']) ? sanitize($_POST['full_name']) : '';
$email = isset($_POST['email']) ? sanitize($_POST['email']) : '';
$phone = isset($_POST['phone']) ? sanitize($_POST['phone']) : '';
$address = isset($_POST['address']) ? sanitize($_POST['address']) : '';
$card_number = isset($_POST['card_number']) ? sanitize($_POST['card_number']) : '';
$card_month = isset($_POST['card_month']) ? sanitize($_POST['card_month']) : '';
$card_year = isset($_POST['card_year']) ? sanitize($_POST['card_year']) : '';
$card_cvc = isset($_POST['card_cvc']) ? sanitize($_POST['card_cvc']) : '';

// Валидация
if (empty($order_id) || empty($full_name) || empty($email) || empty($phone) || 
    empty($card_number) || empty($card_month) || empty($card_year) || empty($card_cvc)) {
    echo json_encode(['success' => false, 'message' => 'Все обязательные поля должны быть заполнены']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Некорректный email адрес']);
    exit;
}

// Валидация карты (упрощенная)
$card_number_clean = preg_replace('/\s+/', '', $card_number);
if (strlen($card_number_clean) < 13 || strlen($card_number_clean) > 19) {
    echo json_encode(['success' => false, 'message' => 'Некорректный номер карты']);
    exit;
}

if (strlen($card_cvc) < 3 || strlen($card_cvc) > 4) {
    echo json_encode(['success' => false, 'message' => 'Некорректный CVC/CVV код']);
    exit;
}

try {
    $pdo = getDBConnection();
    $pdo->beginTransaction();
    
    // Проверяем, что заказ принадлежит пользователю и не оплачен
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ? AND payment_status = 'unpaid'");
    $stmt->execute([$order_id, $_SESSION['user_id']]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Заказ не найден или уже оплачен']);
        exit;
    }
    
    // Обновляем статус оплаты заказа и статус заказа на "подтвержден" (оплачено)
    $stmt = $pdo->prepare("UPDATE orders SET payment_status = 'paid', status = 'confirmed', payment_method = 'card', notes = ? WHERE id = ?");
    // Сохраняем только последние 4 цифры карты, не полные данные
    $notes = "Оплата картой: ****" . substr($card_number_clean, -4) . ", ФИО: $full_name, Email: $email, Телефон: $phone";
    if ($address) {
        $notes .= ", Адрес: $address";
    }
    $result = $stmt->execute([$notes, $order_id]);
    
    if (!$result) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Ошибка при обновлении статуса заказа']);
        exit;
    }
    
    // Получаем элементы заказа для создания подписок (если есть абонементы)
    $stmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ? AND item_type = 'subscription'");
    $stmt->execute([$order_id]);
    $subscription_items = $stmt->fetchAll();
    
    // Если в заказе есть абонементы, проверяем наличие активного абонемента
    if (!empty($subscription_items)) {
        $stmt_check = $pdo->prepare("SELECT id FROM user_subscriptions 
                                     WHERE user_id = ? 
                                     AND is_active = TRUE 
                                     AND end_date >= CURDATE()
                                     LIMIT 1");
        $stmt_check->execute([$_SESSION['user_id']]);
        $active_subscription = $stmt_check->fetch();
        
        if ($active_subscription) {
            $pdo->rollBack();
            echo json_encode([
                'success' => false, 
                'message' => 'У вас уже есть активный абонемент. Один пользователь может иметь только один активный абонемент.'
            ]);
            exit;
        }
    }
    
    // Создаем подписки для каждого купленного абонемента (только после оплаты)
    foreach ($subscription_items as $item) {
        // Проверяем, не создана ли уже подписка для этого заказа
        $stmt_check = $pdo->prepare("SELECT id FROM user_subscriptions WHERE order_id = ? AND subscription_id = ?");
        $stmt_check->execute([$order_id, $item['item_id']]);
        $existing = $stmt_check->fetch();
        
        if (!$existing) {
            // Получаем информацию об абонементе
            $stmt_sub = $pdo->prepare("SELECT duration_days FROM subscriptions WHERE id = ?");
            $stmt_sub->execute([$item['item_id']]);
            $sub = $stmt_sub->fetch();
            
            if ($sub) {
                $start_date = date('Y-m-d');
                $end_date = date('Y-m-d', strtotime("+{$sub['duration_days']} days"));
                
                // Создаем подписку для каждого купленного абонемента (с учетом quantity)
                for ($i = 0; $i < $item['quantity']; $i++) {
                    $stmt_usr = $pdo->prepare("INSERT INTO user_subscriptions (user_id, subscription_id, order_id, start_date, end_date, is_active) VALUES (?, ?, ?, ?, ?, TRUE)");
                    $stmt_usr->execute([
                        $_SESSION['user_id'],
                        $item['item_id'],
                        $order_id,
                        $start_date,
                        $end_date
                    ]);
                }
            }
        }
    }
    
    $pdo->commit();
    
    // Получаем обновленный заказ для возврата total_amount
    $stmt = $pdo->prepare("SELECT total_amount FROM orders WHERE id = ?");
    $stmt->execute([$order_id]);
    $updated_order = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_amount = $updated_order ? $updated_order['total_amount'] : $order['total_amount'];
    
    // Логирование платежа (в реальном проекте здесь должна быть интеграция с платежной системой)
    error_log("Payment processed: Order #$order_id, Amount: $total_amount, Card: ****" . substr($card_number_clean, -4));
    
    echo json_encode([
        'success' => true, 
        'message' => 'Платеж успешно обработан!',
        'order_id' => $order_id,
        'total_amount' => $total_amount
    ]);
    
} catch (PDOException $e) {
    if (isset($pdo)) {
        $pdo->rollBack();
    }
    error_log("Payment error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Ошибка при обработке платежа. Попробуйте позже.']);
}

