<?php
require_once '../config/config.php';
requireAdmin();
$pageTitle = 'Управление абонементами';
include '../includes/header.php';

$message = '';
$error = '';

// Обработка удаления
if (isset($_GET['delete']) && $_GET['delete'] > 0) {
    $delete_id = (int)$_GET['delete'];
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("UPDATE subscriptions SET is_active = 0 WHERE id = ?");
        $stmt->execute([$delete_id]);
        $message = 'Абонемент успешно удален';
    } catch (PDOException $e) {
        $error = 'Ошибка при удалении абонемента';
        error_log("Delete subscription error: " . $e->getMessage());
    }
}

try {
    $pdo = getDBConnection();
    $stmt = $pdo->query("SELECT * FROM subscriptions ORDER BY price");
    $subscriptions = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Subscriptions list error: " . $e->getMessage());
    $subscriptions = [];
}
?>

<h1>Управление абонементами</h1>

<?php if ($message): ?>
    <div class="alert alert-success"><?php echo $message; ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<div class="mb-3">
    <a href="subscription_edit.php" class="btn btn-success">+ Добавить абонемент</a>
</div>

<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Название</th>
                <th>Цена</th>
                <th>Срок действия (дней)</th>
                <th>Посещений</th>
                <th>Включено</th>
                <th>Статус</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($subscriptions as $sub): ?>
                <tr>
                    <td><?php echo $sub['id']; ?></td>
                    <td><?php echo htmlspecialchars($sub['name']); ?></td>
                    <td><?php echo number_format($sub['price'], 2, '.', ' '); ?> ₽</td>
                    <td><?php echo $sub['duration_days']; ?></td>
                    <td><?php echo $sub['visits_count'] ?? 'Безлимит'; ?></td>
                    <td><?php echo htmlspecialchars($sub['services_included'] ?? '-'); ?></td>
                    <td>
                        <span class="badge <?php echo $sub['is_active'] ? 'bg-success' : 'bg-secondary'; ?>">
                            <?php echo $sub['is_active'] ? 'Активен' : 'Неактивен'; ?>
                        </span>
                    </td>
                    <td>
                        <a href="subscription_edit.php?id=<?php echo $sub['id']; ?>" class="btn btn-sm btn-primary">Редактировать</a>
                        <a href="?delete=<?php echo $sub['id']; ?>" class="btn btn-sm btn-danger" 
                           onclick="return confirm('Вы уверены, что хотите удалить этот абонемент?')">Удалить</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>

