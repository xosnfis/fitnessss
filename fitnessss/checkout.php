<?php
require_once 'config/config.php';
requireLogin();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cart_data = $_POST['cart_data'] ?? '[]';
    $cart = json_decode($cart_data, true);
    
    if (empty($cart)) {
        $error = 'Корзина пуста';
    } else {
        try {
            $pdo = getDBConnection();
            $pdo->beginTransaction();
            
            // Расчет общей суммы
            $total_amount = 0;
            foreach ($cart as $item) {
                $total_amount += ($item['price'] ?? 0) * ($item['quantity'] ?? 1);
            }
            
            // Создание заказа
            $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount, status, payment_status) VALUES (?, ?, 'pending', 'unpaid')");
            $stmt->execute([$_SESSION['user_id'], $total_amount]);
            $order_id = $pdo->lastInsertId();
            
            // Добавление элементов заказа
            foreach ($cart as $item) {
                $quantity = $item['quantity'] ?? 1;
                $price = $item['price'] ?? 0;
                $subtotal = $price * $quantity;
                
                $stmt = $pdo->prepare("INSERT INTO order_items (order_id, item_type, item_id, quantity, price, subtotal) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $order_id,
                    $item['type'],
                    $item['id'],
                    $quantity,
                    $price,
                    $subtotal
                ]);
                
                // Если это абонемент, создаем запись в user_subscriptions
                if ($item['type'] === 'subscription') {
                    $stmt_sub = $pdo->prepare("SELECT duration_days FROM subscriptions WHERE id = ?");
                    $stmt_sub->execute([$item['id']]);
                    $sub = $stmt_sub->fetch();
                    
                    if ($sub) {
                        $start_date = date('Y-m-d');
                        $end_date = date('Y-m-d', strtotime("+{$sub['duration_days']} days"));
                        
                        $stmt_usr = $pdo->prepare("INSERT INTO user_subscriptions (user_id, subscription_id, order_id, start_date, end_date, is_active) VALUES (?, ?, ?, ?, ?, TRUE)");
                        $stmt_usr->execute([
                            $_SESSION['user_id'],
                            $item['id'],
                            $order_id,
                            $start_date,
                            $end_date
                        ]);
                    }
                }
            }
            
            $pdo->commit();
            $success = 'Заказ успешно оформлен! Номер заказа: #' . $order_id;
            
            // Очистка корзины
            echo '<script>localStorage.removeItem("cart");</script>';
            
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = 'Ошибка при оформлении заказа. Попробуйте позже.';
            error_log("Checkout error: " . $e->getMessage());
        }
    }
}

$pageTitle = 'Оформление заказа';
include 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
            <a href="cart.php" class="btn btn-primary">Вернуться в корзину</a>
        <?php elseif ($success): ?>
            <div class="alert alert-success">
                <h4><?php echo $success; ?></h4>
                <p>Вы можете просмотреть детали заказа в разделе <a href="orders.php">"Мои заказы"</a></p>
            </div>
            <a href="index.php" class="btn btn-primary">На главную</a>
            <a href="orders.php" class="btn btn-secondary">Мои заказы</a>
        <?php else: ?>
            <div class="alert alert-info">Перенаправление...</div>
            <script>window.location.href = 'cart.php';</script>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

