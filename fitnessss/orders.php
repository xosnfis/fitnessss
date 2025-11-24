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
                            <button class="btn btn-sm btn-info" onclick="showOrderDetails(<?php echo $order['id']; ?>)">
                                <i class="fas fa-info-circle me-1"></i>Детали
                            </button>
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

</script>

<?php include 'includes/footer.php'; ?>

