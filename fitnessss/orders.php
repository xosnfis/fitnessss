<?php
require_once 'config/config.php';
requireLogin();
$pageTitle = 'Мои заказы';
include 'includes/header.php';

try {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT o.*, COUNT(oi.id) as items_count 
                           FROM orders o 
                           LEFT JOIN order_items oi ON o.id = oi.order_id 
                           WHERE o.user_id = ? 
                           GROUP BY o.id 
                           ORDER BY o.order_date DESC");
    $stmt->execute([$_SESSION['user_id']]);
    $orders = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Orders error: " . $e->getMessage());
    $orders = [];
}
?>

<h1 class="mb-4 fade-in-on-scroll">
    <i class="fas fa-list-alt text-primary me-2"></i>Мои заказы
</h1>

<?php if (empty($orders)): ?>
    <div class="alert alert-info fade-in-on-scroll">
        <i class="fas fa-info-circle me-2"></i>У вас пока нет заказов
        <p class="mb-0 mt-2">
            <a href="services.php" class="alert-link">Посмотрите наши услуги</a> или 
            <a href="subscriptions.php" class="alert-link">выберите абонемент</a>
        </p>
    </div>
<?php else: ?>
    <div class="table-responsive fade-in-on-scroll">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>№ заказа</th>
                    <th>Дата</th>
                    <th>Количество товаров</th>
                    <th>Сумма</th>
                    <th>Статус</th>
                    <th>Оплата</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $index => $order): ?>
                    <tr class="fade-in-on-scroll" style="animation-delay: <?php echo $index * 0.1; ?>s;">
                        <td><strong>#<?php echo $order['id']; ?></strong></td>
                        <td><?php echo date('d.m.Y H:i', strtotime($order['order_date'])); ?></td>
                        <td>
                            <i class="fas fa-shopping-bag me-1 text-primary"></i>
                            <?php echo $order['items_count']; ?>
                        </td>
                        <td>
                            <strong class="text-primary fs-5"><?php echo number_format($order['total_amount'], 2, '.', ' '); ?> ₽</strong>
                        </td>
                        <td>
                            <?php
                            $statuses = [
                                'pending' => 'Ожидает',
                                'confirmed' => 'Подтвержден',
                                'paid' => 'Оплачено',
                                'cancelled' => 'Отменен',
                                'completed' => 'Завершен'
                            ];
                            
                            // Если заказ оплачен, показываем статус "Оплачено" с классом status-paid
                            if ($order['payment_status'] === 'paid' && $order['status'] !== 'cancelled' && $order['status'] !== 'completed') {
                                $displayStatus = 'paid';
                                $displayText = 'Оплачено';
                            } else {
                                $displayStatus = $order['status'];
                                $displayText = $statuses[$order['status']] ?? $order['status'];
                            }
                            ?>
                            <span class="order-status status-<?php echo $displayStatus; ?>">
                                <?php echo $displayText; ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($order['payment_status'] === 'paid'): ?>
                                <span class="badge bg-success">
                                    <i class="fas fa-check-circle me-1"></i>Оплачен
                                </span>
                            <?php elseif ($order['payment_status'] === 'refunded'): ?>
                                <span class="badge bg-warning">
                                    <i class="fas fa-undo me-1"></i>Возврат
                                </span>
                            <?php else: ?>
                                <span class="badge bg-danger">
                                    <i class="fas fa-times-circle me-1"></i>Не оплачен
                                </span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="d-flex flex-column gap-2">
                                <button class="btn btn-sm btn-info" onclick="showOrderDetails(<?php echo $order['id']; ?>)">
                                    <i class="fas fa-info-circle me-1"></i>Детали
                                </button>
                                <?php if ($order['payment_status'] === 'unpaid' && $order['status'] !== 'cancelled'): ?>
                                    <button class="btn btn-sm btn-success" onclick="openPaymentModal(<?php echo $order['id']; ?>, <?php echo $order['total_amount']; ?>)">
                                        <i class="fas fa-credit-card me-1"></i>Оплатить
                                    </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<!-- Modal для деталей заказа -->
<div class="modal fade" id="orderModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-info-circle me-2"></i>Детали заказа
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="orderDetails">
                Загрузка...
            </div>
        </div>
    </div>
</div>

<!-- Modal для оплаты заказа -->
<div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="paymentModalLabel">
                    <i class="fas fa-credit-card me-2 text-primary"></i>Оплата заказа
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
            </div>
            <div class="modal-body">
                <div id="paymentOrderInfo" class="mb-4 p-3 bg-light rounded">
                    <!-- Информация о заказе будет загружена через JS -->
                </div>
                
                <form id="paymentForm" method="POST" action="api/process_payment.php">
                    <input type="hidden" id="paymentOrderId" name="order_id" value="">
                    
                    <h6 class="mb-3">
                        <i class="fas fa-user me-2 text-primary"></i>Данные плательщика
                    </h6>
                    <div class="row mb-3">
                        <div class="col-md-6 mb-3">
                            <label for="paymentFullName" class="form-label">ФИО *</label>
                            <input type="text" class="form-control" id="paymentFullName" name="full_name" required 
                                   value="<?php echo htmlspecialchars($_SESSION['full_name'] ?? ''); ?>" 
                                   placeholder="Иванов Иван Иванович">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="paymentEmail" class="form-label">Email *</label>
                            <input type="email" class="form-control" id="paymentEmail" name="email" required 
                                   value="<?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?>" 
                                   placeholder="your@email.com">
                        </div>
                    </div>
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <label for="paymentPhone" class="form-label">Телефон *</label>
                            <input type="tel" class="form-control" id="paymentPhone" name="phone" required 
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
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Отмена
                </button>
                <button type="submit" form="paymentForm" class="btn btn-success">
                    <i class="fas fa-check me-2"></i>Оплатить
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function showOrderDetails(orderId) {
    fetch('api/get_order_details.php?id=' + orderId)
        .then(response => response.json())
        .then(data => {
            let html = '<table class="table"><thead><tr><th>Товар</th><th>Тип</th><th>Количество</th><th>Цена</th><th>Сумма</th></tr></thead><tbody>';
            data.items.forEach(item => {
                html += `<tr>
                    <td>${item.name}</td>
                    <td>${item.item_type === 'service' ? 'Услуга' : 'Абонемент'}</td>
                    <td>${item.quantity}</td>
                    <td>${parseFloat(item.price).toFixed(2)} ₽</td>
                    <td>${parseFloat(item.subtotal).toFixed(2)} ₽</td>
                </tr>`;
            });
            html += '</tbody></table>';
            html += `<p><strong>Итого: ${parseFloat(data.total).toFixed(2)} ₽</strong></p>`;
            document.getElementById('orderDetails').innerHTML = html;
            new bootstrap.Modal(document.getElementById('orderModal')).show();
        })
        .catch(error => {
            document.getElementById('orderDetails').innerHTML = '<div class="alert alert-danger">Ошибка загрузки деталей заказа</div>';
        });
}

function openPaymentModal(orderId, totalAmount) {
    // Устанавливаем ID заказа
    document.getElementById('paymentOrderId').value = orderId;
    
    // Загружаем детали заказа для отображения
    fetch('api/get_order_details.php?id=' + orderId)
        .then(response => response.json())
        .then(data => {
            let html = '<h6 class="mb-2">Заказ #' + orderId + '</h6>';
            html += '<div class="mb-2"><strong>Товары:</strong></div>';
            html += '<ul class="mb-2">';
            data.items.forEach(item => {
                html += `<li>${item.name} - ${item.quantity} шт. × ${parseFloat(item.price).toFixed(2)} ₽</li>`;
            });
            html += '</ul>';
            html += '<div class="mt-3"><strong class="fs-5 text-primary">К оплате: ' + parseFloat(data.total).toFixed(2) + ' ₽</strong></div>';
            document.getElementById('paymentOrderInfo').innerHTML = html;
        })
        .catch(error => {
            document.getElementById('paymentOrderInfo').innerHTML = '<p>Заказ #' + orderId + '</p><p><strong>Сумма: ' + parseFloat(totalAmount).toFixed(2) + ' ₽</strong></p>';
        });
    
    // Открываем модальное окно
    new bootstrap.Modal(document.getElementById('paymentModal')).show();
}

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
            // Закрываем модальное окно оплаты
            const paymentModal = bootstrap.Modal.getInstance(document.getElementById('paymentModal'));
            if (paymentModal) {
                paymentModal.hide();
            }
            
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

// Очистка формы при закрытии модального окна
const paymentModal = document.getElementById('paymentModal');
if (paymentModal) {
    paymentModal.addEventListener('hidden.bs.modal', function() {
        const form = document.getElementById('paymentForm');
        const messageDiv = document.getElementById('paymentFormMessage');
        if (form) form.reset();
        if (messageDiv) messageDiv.innerHTML = '';
    });
}

// Функция показа модального окна благодарности
function showThankYouModal(orderId, totalAmount) {
    // Создаем или обновляем модальное окно
    let thankYouModal = document.getElementById('thankYouModal');
    if (!thankYouModal) {
        const modalHtml = `
            <div class="modal fade" id="thankYouModal" tabindex="-1" aria-labelledby="thankYouModalLabel" aria-hidden="true" data-bs-backdrop="static">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content thank-you-modal">
                        <div class="modal-body text-center p-5">
                            <div class="thank-you-icon mb-4">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <h2 class="thank-you-title mb-3">Спасибо за покупку!</h2>
                            <p class="lead mb-4 thank-you-order">Ваш заказ #${orderId} успешно оплачен</p>
                            <div class="card thank-you-card mb-4">
                                <div class="card-body p-4">
                                    <p class="mb-2 text-muted"><strong>Сумма оплаты:</strong></p>
                                    <h3 class="thank-you-amount mb-0">${parseFloat(totalAmount).toFixed(2)} ₽</h3>
                                </div>
                            </div>
                            <div class="thank-you-message mb-4">
                                <p class="mb-2">
                                    <i class="fas fa-envelope text-primary me-2"></i>
                                    Чек отправлен на вашу почту
                                </p>
                                <p class="mb-0 text-muted">
                                    Мы свяжемся с вами в ближайшее время для подтверждения заказа
                                </p>
                            </div>
                            <button type="button" class="btn btn-primary btn-lg px-5" data-bs-dismiss="modal" onclick="location.reload()">
                                <i class="fas fa-check me-2"></i>Отлично!
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        thankYouModal = document.getElementById('thankYouModal');
    } else {
        // Обновляем данные если модальное окно уже существует
        const orderIdElement = thankYouModal.querySelector('.thank-you-order');
        const amountElement = thankYouModal.querySelector('.thank-you-amount');
        if (orderIdElement) orderIdElement.textContent = `Ваш заказ #${orderId} успешно оплачен`;
        if (amountElement) amountElement.textContent = parseFloat(totalAmount).toFixed(2) + ' ₽';
    }
    
    // Показываем модальное окно
    const bsModal = new bootstrap.Modal(thankYouModal);
    bsModal.show();
    
    // Перезагружаем страницу после закрытия модального окна
    thankYouModal.addEventListener('hidden.bs.modal', function() {
        location.reload();
    }, { once: true });
}
</script>

<?php include 'includes/footer.php'; ?>

