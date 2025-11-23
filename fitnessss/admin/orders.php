<?php
require_once '../config/config.php';
requireAdmin();
$pageTitle = 'Управление заказами';
include 'includes/header.php';

$message = '';
$error = '';

// Обработка изменения статуса
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {
    $order_id = (int)$_POST['order_id'];
    $status = sanitize($_POST['status'] ?? '');
    $payment_status = sanitize($_POST['payment_status'] ?? '');
    
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("UPDATE orders SET status = ?, payment_status = ? WHERE id = ?");
        $stmt->execute([$status, $payment_status, $order_id]);
        $message = 'Статус заказа обновлен';
    } catch (PDOException $e) {
        $error = 'Ошибка при обновлении статуса';
        error_log("Update order error: " . $e->getMessage());
    }
}

try {
    $pdo = getDBConnection();
    $stmt = $pdo->query("SELECT o.*, u.username, u.full_name, u.email, COUNT(oi.id) as items_count 
                         FROM orders o 
                         LEFT JOIN users u ON o.user_id = u.id 
                         LEFT JOIN order_items oi ON o.id = oi.order_id 
                         GROUP BY o.id 
                         ORDER BY o.order_date DESC");
    $orders = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Orders list error: " . $e->getMessage());
    $orders = [];
}
?>

<h1 class="admin-page-title fade-in-on-scroll">
    <i class="fas fa-shopping-bag"></i>Управление заказами
</h1>

<?php if ($message): ?>
    <div class="alert alert-success fade-in-on-scroll">
        <i class="fas fa-check-circle me-2"></i><?php echo $message; ?>
    </div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger fade-in-on-scroll">
        <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
    </div>
<?php endif; ?>

<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Пользователь</th>
                <th>Дата</th>
                <th>Товаров</th>
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
                    <td>
                        <?php echo htmlspecialchars($order['full_name']); ?><br>
                        <small class="text-muted"><?php echo htmlspecialchars($order['username']); ?></small>
                    </td>
                    <td><?php echo date('d.m.Y H:i', strtotime($order['order_date'])); ?></td>
                    <td><?php echo $order['items_count']; ?></td>
                    <td><strong><?php echo number_format($order['total_amount'], 2, '.', ' '); ?> ₽</strong></td>
                    <td>
                        <form method="POST" class="d-inline">
                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                            <select name="status" class="form-select form-select-sm d-inline-block" style="width: auto;" 
                                    onchange="this.form.submit()">
                                <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Ожидает</option>
                                <option value="confirmed" <?php echo $order['status'] === 'confirmed' ? 'selected' : ''; ?>>Подтвержден</option>
                                <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Отменен</option>
                                <option value="completed" <?php echo $order['status'] === 'completed' ? 'selected' : ''; ?>>Завершен</option>
                            </select>
                            <input type="hidden" name="payment_status" value="<?php echo $order['payment_status']; ?>">
                        </form>
                    </td>
                    <td>
                        <form method="POST" class="d-inline">
                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                            <select name="payment_status" class="form-select form-select-sm d-inline-block" style="width: auto;" 
                                    onchange="this.form.submit()">
                                <option value="unpaid" <?php echo $order['payment_status'] === 'unpaid' ? 'selected' : ''; ?>>Не оплачен</option>
                                <option value="paid" <?php echo $order['payment_status'] === 'paid' ? 'selected' : ''; ?>>Оплачен</option>
                                <option value="refunded" <?php echo $order['payment_status'] === 'refunded' ? 'selected' : ''; ?>>Возврат</option>
                            </select>
                            <input type="hidden" name="status" value="<?php echo $order['status']; ?>">
                        </form>
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
    fetch('../api/get_order_details.php?id=' + orderId)
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

