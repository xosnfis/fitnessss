<?php
require_once 'config/config.php';
requireLogin();

$error = '';
$order_id = null;
$order_data = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Проверяем, это оплата или оформление заказа
    if (isset($_POST['order_id']) && isset($_POST['card_number'])) {
        // Это оплата - обрабатываем через API
        header('Content-Type: application/json');
        require_once 'api/process_payment.php';
        exit;
    }
    
    // Это оформление заказа
    $cart_data = $_POST['cart_data'] ?? '[]';
    $cart = json_decode($cart_data, true);
    
    if (empty($cart)) {
        $error = 'Корзина пуста';
    } else {
        try {
            $pdo = getDBConnection();
            
            // Проверяем, есть ли в корзине абонемент
            $has_subscription = false;
            foreach ($cart as $item) {
                if (isset($item['type']) && $item['type'] === 'subscription') {
                    $has_subscription = true;
                    break;
                }
            }
            
            // Если в корзине есть абонемент, проверяем наличие активного абонемента
            if ($has_subscription) {
                $stmt = $pdo->prepare("SELECT id FROM user_subscriptions 
                                       WHERE user_id = ? 
                                       AND is_active = TRUE 
                                       AND end_date >= CURDATE()
                                       LIMIT 1");
                $stmt->execute([$_SESSION['user_id']]);
                $active_subscription = $stmt->fetch();
                
                if ($active_subscription) {
                    $error = 'У вас уже есть активный абонемент. Один пользователь может иметь только один активный абонемент.';
                }
            }
            
            if ($error) {
                // Если есть ошибка, не создаем заказ
            } else {
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
                }
                
                $pdo->commit();
                
                // Получаем данные заказа для отображения
                $stmt = $pdo->prepare("SELECT o.*, 
                    (SELECT GROUP_CONCAT(CONCAT(oi.quantity, 'x ', 
                        CASE 
                            WHEN oi.item_type = 'service' THEN s.name 
                            WHEN oi.item_type = 'subscription' THEN sub.name 
                        END
                    ) SEPARATOR ', ')
                    FROM order_items oi
                    LEFT JOIN services s ON oi.item_type = 'service' AND oi.item_id = s.id
                    LEFT JOIN subscriptions sub ON oi.item_type = 'subscription' AND oi.item_id = sub.id
                    WHERE oi.order_id = o.id) as items_info
                    FROM orders o WHERE o.id = ?");
                $stmt->execute([$order_id]);
                $order_data = $stmt->fetch();
                
                // Очистка корзины
                echo '<script>localStorage.removeItem("cart");</script>';
            }
            
        } catch (PDOException $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error = 'Ошибка при оформлении заказа. Попробуйте позже.';
            error_log("Checkout error: " . $e->getMessage());
        }
    }
}

$pageTitle = 'Оформление заказа';
include 'includes/header.php';

// Получаем данные пользователя для формы
try {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
} catch (PDOException $e) {
    $user = null;
}
?>

<div class="row justify-content-center">
    <div class="col-md-10">
        <?php if ($error): ?>
            <div class="card fade-in-on-scroll">
                <div class="card-body p-4 text-center">
                    <div class="alert alert-danger fade-in-on-scroll">
                        <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                    </div>
                    <a href="cart.php" class="btn btn-primary">
                        <i class="fas fa-arrow-left me-2"></i>Вернуться в корзину
                    </a>
                </div>
            </div>
        <?php elseif ($order_id && $order_data): ?>
            <!-- Форма оплаты -->
            <div class="card fade-in-on-scroll">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-credit-card me-2"></i>Оплата заказа #<?php echo $order_id; ?>
                    </h4>
                </div>
                <div class="card-body p-4">
                    <!-- Информация о заказе -->
                    <div class="mb-4 p-3 bg-light rounded">
                        <h6 class="mb-2">Заказ #<?php echo $order_id; ?></h6>
                        <div class="mb-2"><strong>Товары:</strong></div>
                        <div id="order-items-info" class="mb-2">
                            <?php
                            // Получаем детали заказа
                            try {
                                $stmt = $pdo->prepare("SELECT oi.*, 
                                    CASE 
                                        WHEN oi.item_type = 'service' THEN s.name 
                                        WHEN oi.item_type = 'subscription' THEN sub.name 
                                    END as item_name
                                    FROM order_items oi
                                    LEFT JOIN services s ON oi.item_type = 'service' AND oi.item_id = s.id
                                    LEFT JOIN subscriptions sub ON oi.item_type = 'subscription' AND oi.item_id = sub.id
                                    WHERE oi.order_id = ?");
                                $stmt->execute([$order_id]);
                                $items = $stmt->fetchAll();
                                
                                echo '<ul class="mb-0">';
                                foreach ($items as $item) {
                                    echo '<li>' . htmlspecialchars($item['item_name']) . ' - ' . $item['quantity'] . ' шт. × ' . number_format($item['price'], 2, '.', ' ') . ' ₽</li>';
                                }
                                echo '</ul>';
                            } catch (PDOException $e) {
                                echo '<p>Ошибка загрузки товаров</p>';
                            }
                            ?>
                        </div>
                        <div class="mt-3">
                            <strong class="fs-5 text-primary">К оплате: <?php echo number_format($order_data['total_amount'], 2, '.', ' '); ?> ₽</strong>
                        </div>
                    </div>
                    
                    <!-- Форма оплаты -->
                    <form id="paymentForm" method="POST" action="">
                        <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                        
                        <h6 class="mb-3">
                            <i class="fas fa-user me-2 text-primary"></i>Данные плательщика
                        </h6>
                        <div class="row mb-3">
                            <div class="col-md-6 mb-3">
                                <label for="paymentFullName" class="form-label">ФИО *</label>
                                <input type="text" class="form-control" id="paymentFullName" name="full_name" required 
                                       value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>" 
                                       placeholder="Иванов Иван Иванович">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="paymentEmail" class="form-label">Email *</label>
                                <input type="email" class="form-control" id="paymentEmail" name="email" required 
                                       value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" 
                                       placeholder="your@email.com">
                            </div>
                        </div>
                        <div class="row mb-4">
                            <div class="col-md-6 mb-3">
                                <label for="paymentPhone" class="form-label">Телефон *</label>
                                <input type="tel" class="form-control" id="paymentPhone" name="phone" required 
                                       value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"
                                       placeholder="+7 (999) 123-45-67">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="paymentAddress" class="form-label">Адрес</label>
                                <input type="text" class="form-control" id="paymentAddress" name="address" 
                                       placeholder="Город, улица, дом">
                            </div>
                        </div>
                        
                        <h6 class="mb-3">
                            <i class="fas fa-credit-card me-2 text-primary"></i>Данные карты
                        </h6>
                        <div class="row mb-3">
                            <div class="col-md-12 mb-3">
                                <label for="paymentCardNumber" class="form-label">Номер карты *</label>
                                <input type="text" class="form-control" id="paymentCardNumber" name="card_number" required 
                                       maxlength="19" placeholder="0000 0000 0000 0000"
                                       pattern="[0-9\s]{13,19}">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4 mb-3">
                                <label for="paymentCardMonth" class="form-label">Месяц *</label>
                                <select class="form-select" id="paymentCardMonth" name="card_month" required>
                                    <option value="">ММ</option>
                                    <?php for ($i = 1; $i <= 12; $i++): ?>
                                        <option value="<?php echo str_pad($i, 2, '0', STR_PAD_LEFT); ?>">
                                            <?php echo str_pad($i, 2, '0', STR_PAD_LEFT); ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="paymentCardYear" class="form-label">Год *</label>
                                <select class="form-select" id="paymentCardYear" name="card_year" required>
                                    <option value="">ГГГГ</option>
                                    <?php 
                                    $currentYear = date('Y');
                                    for ($i = $currentYear; $i <= $currentYear + 10; $i++): 
                                    ?>
                                        <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="paymentCardCVC" class="form-label">CVC/CVV *</label>
                                <input type="text" class="form-control" id="paymentCardCVC" name="card_cvc" required 
                                       maxlength="4" placeholder="123" pattern="[0-9]{3,4}">
                            </div>
                        </div>
                        
                        <div id="paymentFormMessage"></div>
                        
                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="cart.php" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Отмена
                            </a>
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-check me-2"></i>Оплатить
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-info fade-in-on-scroll">
                <i class="fas fa-spinner fa-spin me-2"></i>Перенаправление...
            </div>
            <script>window.location.href = 'cart.php';</script>
        <?php endif; ?>
    </div>
</div>

<script>
// Обработка формы оплаты
const paymentForm = document.getElementById('paymentForm');
if (paymentForm) {
    paymentForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const form = this;
        const formData = new FormData(form);
        const submitBtn = form.querySelector('button[type="submit"]');
        const messageDiv = document.getElementById('paymentFormMessage');
        const originalBtnText = submitBtn.innerHTML;
        
        // Блокируем кнопку
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Обработка...';
        messageDiv.innerHTML = '';
        
        // Отправка формы через AJAX
        fetch('api/process_payment.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('HTTP error! status: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            console.log('Payment response:', data);
            if (data.success) {
                // Показываем модальное окно благодарности
                showThankYouModal(data.order_id, data.total_amount);
            } else {
                messageDiv.innerHTML = '<div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i>' + (data.message || 'Ошибка при обработке платежа') + '</div>';
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            }
        })
        .catch(error => {
            console.error('Payment error:', error);
            messageDiv.innerHTML = '<div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i>Произошла ошибка при обработке платежа: ' + error.message + '. Попробуйте позже.</div>';
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
        });
    });
}

// Форматирование номера карты
const paymentCardNumber = document.getElementById('paymentCardNumber');
if (paymentCardNumber) {
    paymentCardNumber.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\s/g, '');
        let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
        e.target.value = formattedValue;
    });
}

// Функция показа модального окна благодарности
function showThankYouModal(orderId, totalAmount) {
    // Удаляем старое модальное окно если оно есть
    const oldModal = document.getElementById('thankYouModal');
    if (oldModal) {
        oldModal.remove();
    }
    
    // Создаем новое модальное окно
    const modalHtml = `
        <div class="modal fade" id="thankYouModal" tabindex="-1" aria-labelledby="thankYouModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header border-0 pb-0">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                    </div>
                    <div class="modal-body text-center p-5 pt-0">
                        <div class="mb-4" style="font-size: 4rem; color: #28a745;">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <h2 class="mb-3">Спасибо за покупку!</h2>
                        <p class="lead mb-4">Ваш заказ #${orderId} успешно оплачен</p>
                        <div class="card mb-4">
                            <div class="card-body p-4">
                                <p class="mb-2 text-muted"><strong>Сумма оплаты:</strong></p>
                                <h3 class="mb-0 text-primary">${parseFloat(totalAmount).toFixed(2)} ₽</h3>
                            </div>
                        </div>
                        <div class="mb-4">
                            <p class="mb-2">
                                <i class="fas fa-envelope text-primary me-2"></i>
                                Чек отправлен на вашу почту
                            </p>
                            <p class="mb-0 text-muted">
                                Мы свяжемся с вами в ближайшее время для подтверждения заказа
                            </p>
                        </div>
                        <button type="button" class="btn btn-primary btn-lg px-5" data-bs-dismiss="modal" onclick="setTimeout(() => window.location.href = 'orders.php', 100)">
                            <i class="fas fa-check me-2"></i>Отлично!
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    const thankYouModal = document.getElementById('thankYouModal');
    
    // Показываем модальное окно
    const bsModal = new bootstrap.Modal(thankYouModal);
    bsModal.show();
    
    // Переходим на страницу заказов при закрытии окна
    thankYouModal.addEventListener('hidden.bs.modal', function() {
        window.location.href = 'orders.php';
    }, { once: true });
}
</script>

<?php include 'includes/footer.php'; ?>
