<?php
require_once '../config/config.php';
requireAdmin();
$pageTitle = 'Управление расписанием';
include '../includes/header.php';

$message = '';
$error = '';

// Обработка обновления расписания
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['service_id'])) {
        // Обновление расписания услуги
        $service_id = (int)$_POST['service_id'];
        $schedule = sanitize($_POST['schedule'] ?? '');
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("UPDATE services SET schedule = ? WHERE id = ?");
            $stmt->execute([$schedule, $service_id]);
            $message = 'Расписание услуги обновлено';
        } catch (PDOException $e) {
            $error = 'Ошибка при обновлении расписания';
            error_log("Update schedule error: " . $e->getMessage());
        }
    } elseif (isset($_POST['trainer_id'])) {
        // Обновление расписания тренера
        $trainer_id = (int)$_POST['trainer_id'];
        $schedule = sanitize($_POST['schedule'] ?? '');
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("UPDATE trainers SET schedule = ? WHERE id = ?");
            $stmt->execute([$schedule, $trainer_id]);
            $message = 'Расписание тренера обновлено';
        } catch (PDOException $e) {
            $error = 'Ошибка при обновлении расписания';
            error_log("Update schedule error: " . $e->getMessage());
        }
    }
}

try {
    $pdo = getDBConnection();
    
    // Получение услуг с расписанием
    $stmt = $pdo->query("SELECT s.*, t.full_name as trainer_name FROM services s 
                         LEFT JOIN trainers t ON s.trainer_id = t.id 
                         ORDER BY s.name");
    $services = $stmt->fetchAll();
    
    // Получение тренеров с расписанием
    $stmt = $pdo->query("SELECT * FROM trainers ORDER BY full_name");
    $trainers = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Schedule error: " . $e->getMessage());
    $services = [];
    $trainers = [];
}
?>

<h1>Редактирование расписания</h1>

<?php if ($message): ?>
    <div class="alert alert-success"><?php echo $message; ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<div class="row">
    <div class="col-md-6">
        <h2>Расписание услуг</h2>
        <?php foreach ($services as $service): ?>
            <div class="card mb-3">
                <div class="card-body">
                    <h5><?php echo htmlspecialchars($service['name']); ?></h5>
                    <?php if ($service['trainer_name']): ?>
                        <p class="text-muted">Тренер: <?php echo htmlspecialchars($service['trainer_name']); ?></p>
                    <?php endif; ?>
                    <form method="POST" action="">
                        <input type="hidden" name="service_id" value="<?php echo $service['id']; ?>">
                        <div class="mb-3">
                            <label class="form-label">Расписание</label>
                            <input type="text" class="form-control" name="schedule" 
                                   value="<?php echo htmlspecialchars($service['schedule'] ?? ''); ?>"
                                   placeholder="Например: Пн, Ср, Пт 18:00-19:00">
                        </div>
                        <button type="submit" class="btn btn-sm btn-primary">Сохранить</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <div class="col-md-6">
        <h2>Расписание тренеров</h2>
        <?php foreach ($trainers as $trainer): ?>
            <div class="card mb-3">
                <div class="card-body">
                    <h5><?php echo htmlspecialchars($trainer['full_name']); ?></h5>
                    <p class="text-muted"><?php echo htmlspecialchars($trainer['specialization']); ?></p>
                    <form method="POST" action="">
                        <input type="hidden" name="trainer_id" value="<?php echo $trainer['id']; ?>">
                        <div class="mb-3">
                            <label class="form-label">Расписание работы</label>
                            <input type="text" class="form-control" name="schedule" 
                                   value="<?php echo htmlspecialchars($trainer['schedule'] ?? ''); ?>"
                                   placeholder="Например: Пн-Пт: 10:00-18:00">
                        </div>
                        <button type="submit" class="btn btn-sm btn-primary">Сохранить</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

