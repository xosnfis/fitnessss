<?php
require_once '../config/config.php';
requireAdmin();
$pageTitle = 'Админ-панель';
include '../includes/header.php';

try {
    $pdo = getDBConnection();
    
    // Статистика
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE role = 'user'");
    $users_count = $stmt->fetch()['count'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM orders");
    $orders_count = $stmt->fetch()['count'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM services WHERE is_active = 1");
    $services_count = $stmt->fetch()['count'];
    
    $stmt = $pdo->query("SELECT SUM(total_amount) as total FROM orders WHERE payment_status = 'paid'");
    $revenue = $stmt->fetch()['total'] ?? 0;
} catch (PDOException $e) {
    error_log("Admin dashboard error: " . $e->getMessage());
    $users_count = $orders_count = $services_count = $revenue = 0;
}
?>

<div class="admin-panel">
    <h1>Админ-панель</h1>
    
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3><?php echo $users_count; ?></h3>
                    <p>Пользователей</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3><?php echo $orders_count; ?></h3>
                    <p>Заказов</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3><?php echo $services_count; ?></h3>
                    <p>Услуг</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3><?php echo number_format($revenue, 2, '.', ' '); ?> ₽</h3>
                    <p>Выручка</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="list-group">
        <a href="users.php" class="list-group-item list-group-item-action">
            <h5>👥 Управление пользователями</h5>
            <p class="mb-0">Просмотр, добавление, редактирование и удаление пользователей</p>
        </a>
        <a href="services.php" class="list-group-item list-group-item-action">
            <h5>💼 Управление услугами</h5>
            <p class="mb-0">Добавление, редактирование и удаление услуг</p>
        </a>
        <a href="trainers.php" class="list-group-item list-group-item-action">
            <h5>👨‍🏫 Управление тренерами</h5>
            <p class="mb-0">Добавление, редактирование и удаление тренеров</p>
        </a>
        <a href="subscriptions.php" class="list-group-item list-group-item-action">
            <h5>🎫 Управление абонементами</h5>
            <p class="mb-0">Управление абонементами и ценами</p>
        </a>
        <a href="orders.php" class="list-group-item list-group-item-action">
            <h5>📦 Просмотр заказов</h5>
            <p class="mb-0">Просмотр всех заказов и управление их статусами</p>
        </a>
        <a href="schedule.php" class="list-group-item list-group-item-action">
            <h5>📅 Редактирование расписания</h5>
            <p class="mb-0">Управление расписанием занятий и тренеров</p>
        </a>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

