<?php
require_once '../config/config.php';
requireAdmin();
$pageTitle = 'Админ-панель';
include 'includes/header.php';

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

<h1 class="admin-page-title fade-in-on-scroll">
    <i class="fas fa-tachometer-alt"></i>Админ-панель
</h1>

<div class="row mb-5">
    <div class="col-md-3 col-sm-6 mb-4 fade-in-on-scroll">
        <div class="admin-stat-card">
            <i class="fas fa-users fa-2x mb-2"></i>
            <h3><?php echo $users_count; ?></h3>
            <p>Пользователей</p>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-4 fade-in-on-scroll">
        <div class="admin-stat-card secondary">
            <i class="fas fa-shopping-cart fa-2x mb-2"></i>
            <h3><?php echo $orders_count; ?></h3>
            <p>Заказов</p>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-4 fade-in-on-scroll">
        <div class="admin-stat-card success">
            <i class="fas fa-dumbbell fa-2x mb-2"></i>
            <h3><?php echo $services_count; ?></h3>
            <p>Услуг</p>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-4 fade-in-on-scroll">
        <div class="admin-stat-card warning">
            <i class="fas fa-ruble-sign fa-2x mb-2"></i>
            <h3><?php echo number_format($revenue, 0, '.', ' '); ?></h3>
            <p>Выручка (₽)</p>
        </div>
    </div>
</div>

<h2 class="mb-4 fade-in-on-scroll">
    <i class="fas fa-cog text-primary me-2"></i>Управление системой
</h2>

<div class="row">
    <div class="col-md-6 col-lg-4 mb-4 fade-in-on-scroll">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="me-3" style="font-size: 2.5rem; color: var(--primary-color);">
                        <i class="fas fa-users"></i>
                    </div>
                    <h5 class="card-title mb-0">Управление пользователями</h5>
                </div>
                <p class="card-text">Просмотр, добавление, редактирование и удаление пользователей системы</p>
                <a href="users.php" class="btn btn-primary w-100">
                    <i class="fas fa-arrow-right me-2"></i>Перейти
                </a>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-4 mb-4 fade-in-on-scroll">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="me-3" style="font-size: 2.5rem; color: var(--primary-color);">
                        <i class="fas fa-dumbbell"></i>
                    </div>
                    <h5 class="card-title mb-0">Управление услугами</h5>
                </div>
                <p class="card-text">Добавление, редактирование и удаление услуг фитнес-центра</p>
                <a href="services.php" class="btn btn-primary w-100">
                    <i class="fas fa-arrow-right me-2"></i>Перейти
                </a>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-4 mb-4 fade-in-on-scroll">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="me-3" style="font-size: 2.5rem; color: var(--primary-color);">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <h5 class="card-title mb-0">Управление тренерами</h5>
                </div>
                <p class="card-text">Добавление, редактирование и удаление тренеров</p>
                <a href="trainers.php" class="btn btn-primary w-100">
                    <i class="fas fa-arrow-right me-2"></i>Перейти
                </a>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-4 mb-4 fade-in-on-scroll">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="me-3" style="font-size: 2.5rem; color: var(--primary-color);">
                        <i class="fas fa-id-card"></i>
                    </div>
                    <h5 class="card-title mb-0">Управление абонементами</h5>
                </div>
                <p class="card-text">Управление абонементами и их ценами</p>
                <a href="subscriptions.php" class="btn btn-primary w-100">
                    <i class="fas fa-arrow-right me-2"></i>Перейти
                </a>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-4 mb-4 fade-in-on-scroll">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="me-3" style="font-size: 2.5rem; color: var(--primary-color);">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                    <h5 class="card-title mb-0">Просмотр заказов</h5>
                </div>
                <p class="card-text">Просмотр всех заказов и управление их статусами</p>
                <a href="orders.php" class="btn btn-primary w-100">
                    <i class="fas fa-arrow-right me-2"></i>Перейти
                </a>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-4 mb-4 fade-in-on-scroll">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="me-3" style="font-size: 2.5rem; color: var(--primary-color);">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <h5 class="card-title mb-0">Редактирование расписания</h5>
                </div>
                <p class="card-text">Управление расписанием занятий и тренеров</p>
                <a href="schedule.php" class="btn btn-primary w-100">
                    <i class="fas fa-arrow-right me-2"></i>Перейти
                </a>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-4 mb-4 fade-in-on-scroll">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="me-3" style="font-size: 2.5rem; color: var(--primary-color);">
                        <i class="fas fa-certificate"></i>
                    </div>
                    <h5 class="card-title mb-0">Управление сертификатами</h5>
                </div>
                <p class="card-text">Загрузка и управление сертификатами фитнес-центра</p>
                <a href="certificates.php" class="btn btn-primary w-100">
                    <i class="fas fa-arrow-right me-2"></i>Перейти
                </a>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

