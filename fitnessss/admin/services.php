<?php
require_once '../config/config.php';
requireAdmin();
$pageTitle = 'Управление услугами';
include '../includes/header.php';

$message = '';
$error = '';

// Обработка удаления
if (isset($_GET['delete']) && $_GET['delete'] > 0) {
    $delete_id = (int)$_GET['delete'];
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("UPDATE services SET is_active = 0 WHERE id = ?");
        $stmt->execute([$delete_id]);
        $message = 'Услуга успешно удалена';
    } catch (PDOException $e) {
        $error = 'Ошибка при удалении услуги';
        error_log("Delete service error: " . $e->getMessage());
    }
}

try {
    $pdo = getDBConnection();
    $stmt = $pdo->query("SELECT s.*, t.full_name as trainer_name FROM services s 
                         LEFT JOIN trainers t ON s.trainer_id = t.id 
                         ORDER BY s.name");
    $services = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Services list error: " . $e->getMessage());
    $services = [];
}
?>

<h1>Управление услугами</h1>

<?php if ($message): ?>
    <div class="alert alert-success"><?php echo $message; ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<div class="mb-3">
    <a href="service_edit.php" class="btn btn-success">+ Добавить услугу</a>
</div>

<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Название</th>
                <th>Категория</th>
                <th>Тренер</th>
                <th>Цена</th>
                <th>Длительность</th>
                <th>Расписание</th>
                <th>Статус</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($services as $service): ?>
                <tr>
                    <td><?php echo $service['id']; ?></td>
                    <td><?php echo htmlspecialchars($service['name']); ?></td>
                    <td><?php echo htmlspecialchars($service['category']); ?></td>
                    <td><?php echo htmlspecialchars($service['trainer_name'] ?? '-'); ?></td>
                    <td><?php echo number_format($service['price'], 2, '.', ' '); ?> ₽</td>
                    <td><?php echo $service['duration']; ?> мин</td>
                    <td><?php echo htmlspecialchars($service['schedule'] ?? '-'); ?></td>
                    <td>
                        <span class="badge <?php echo $service['is_active'] ? 'bg-success' : 'bg-secondary'; ?>">
                            <?php echo $service['is_active'] ? 'Активна' : 'Неактивна'; ?>
                        </span>
                    </td>
                    <td>
                        <a href="service_edit.php?id=<?php echo $service['id']; ?>" class="btn btn-sm btn-primary">Редактировать</a>
                        <a href="?delete=<?php echo $service['id']; ?>" class="btn btn-sm btn-danger" 
                           onclick="return confirm('Вы уверены, что хотите удалить эту услугу?')">Удалить</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>

