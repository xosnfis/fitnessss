<?php
require_once '../config/config.php';
requireAdmin();
$pageTitle = 'Управление тренерами';
include 'includes/header.php';

$message = '';
$error = '';

// Обработка удаления
if (isset($_GET['delete']) && $_GET['delete'] > 0) {
    $delete_id = (int)$_GET['delete'];
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("DELETE FROM trainers WHERE id = ?");
        $stmt->execute([$delete_id]);
        $message = 'Тренер успешно удален';
    } catch (PDOException $e) {
        $error = 'Ошибка при удалении тренера. Возможно, с ним связаны услуги.';
        error_log("Delete trainer error: " . $e->getMessage());
    }
}

try {
    $pdo = getDBConnection();
    $stmt = $pdo->query("SELECT * FROM trainers ORDER BY full_name");
    $trainers = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Trainers list error: " . $e->getMessage());
    $trainers = [];
}
?>

<h1 class="admin-page-title fade-in-on-scroll">
    <i class="fas fa-user-tie"></i>Управление тренерами
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

<div class="mb-4 fade-in-on-scroll">
    <a href="trainer_edit.php" class="btn btn-success">
        <i class="fas fa-plus me-2"></i>Добавить тренера
    </a>
</div>

<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>ФИО</th>
                <th>Специализация</th>
                <th>Опыт (лет)</th>
                <th>Телефон</th>
                <th>Email</th>
                <th>Расписание</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($trainers as $trainer): ?>
                <tr>
                    <td><?php echo $trainer['id']; ?></td>
                    <td><?php echo htmlspecialchars($trainer['full_name']); ?></td>
                    <td><?php echo htmlspecialchars($trainer['specialization']); ?></td>
                    <td><?php echo $trainer['experience']; ?></td>
                    <td><?php echo htmlspecialchars($trainer['phone'] ?? '-'); ?></td>
                    <td><?php echo htmlspecialchars($trainer['email'] ?? '-'); ?></td>
                    <td><?php echo htmlspecialchars($trainer['schedule'] ?? '-'); ?></td>
                    <td>
                        <a href="trainer_edit.php?id=<?php echo $trainer['id']; ?>" class="btn btn-sm btn-primary">Редактировать</a>
                        <a href="?delete=<?php echo $trainer['id']; ?>" class="btn btn-sm btn-danger" 
                           onclick="return confirm('Вы уверены, что хотите удалить этого тренера?')">Удалить</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include 'includes/footer.php'; ?>

