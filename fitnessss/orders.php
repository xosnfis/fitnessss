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

<h1>Мои заказы</h1>

<?php if (empty($orders)): ?>
    <div class="alert alert-info">У вас пока нет заказов</div>
<?php else: ?>
    <div class="table-responsive">
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
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td>#<?php echo $order['id']; ?></td>
                        <td><?php echo date('d.m.Y H:i', strtotime($order['order_date'])); ?></td>
                        <td><?php echo $order['items_count']; ?></td>
                        <td><strong><?php echo number_format($order['total_amount'], 2, '.', ' '); ?> ₽</strong></td>
                        <td>
                            <span class="order-status status-<?php echo $order['status']; ?>">
                                <?php
                                $statuses = [
                                    'pending' => 'Ожидает',
                                    'confirmed' => 'Подтвержден',
                                    'cancelled' => 'Отменен',
                                    'completed' => 'Завершен'
                                ];
                                echo $statuses[$order['status']] ?? $order['status'];
                                ?>
                            </span>
                        </td>
                        <td>
                            <?php
                            $payment_statuses = [
                                'unpaid' => 'Не оплачен',
                                'paid' => 'Оплачен',
                                'refunded' => 'Возврат'
                            ];
                            echo $payment_statuses[$order['payment_status']] ?? $order['payment_status'];
                            ?>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-info" onclick="showOrderDetails(<?php echo $order['id']; ?>)">
                                Детали
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
                <h5 class="modal-title">Детали заказа</h5>
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

