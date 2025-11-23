<?php
require_once '../config/config.php';
requireAdmin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$is_edit = $id > 0;

$error = '';
$message = '';

$pdo = getDBConnection();

// Загрузка данных тренера
$trainer = null;
if ($is_edit) {
    $stmt = $pdo->prepare("SELECT * FROM trainers WHERE id = ?");
    $stmt->execute([$id]);
    $trainer = $stmt->fetch();
    if (!$trainer) {
        $error = 'Тренер не найден';
    }
}

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitize($_POST['full_name'] ?? '');
    $specialization = sanitize($_POST['specialization'] ?? '');
    $experience = (int)($_POST['experience'] ?? 0);
    $phone = sanitize($_POST['phone'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $bio = sanitize($_POST['bio'] ?? '');
    $schedule = sanitize($_POST['schedule'] ?? '');
    
    if (empty($full_name) || empty($specialization) || $experience < 0) {
        $error = 'Заполните все обязательные поля';
    } else {
        try {
            if ($is_edit) {
                $stmt = $pdo->prepare("UPDATE trainers SET full_name = ?, specialization = ?, experience = ?, phone = ?, email = ?, bio = ?, schedule = ? WHERE id = ?");
                $stmt->execute([$full_name, $specialization, $experience, $phone, $email, $bio, $schedule, $id]);
                $message = 'Тренер успешно обновлен';
            } else {
                $stmt = $pdo->prepare("INSERT INTO trainers (full_name, specialization, experience, phone, email, bio, schedule) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$full_name, $specialization, $experience, $phone, $email, $bio, $schedule]);
                $message = 'Тренер успешно создан';
                $is_edit = true;
                $id = $pdo->lastInsertId();
            }
        } catch (PDOException $e) {
            $error = 'Ошибка при сохранении тренера';
            error_log("Trainer edit error: " . $e->getMessage());
        }
    }
    
    if ($message && !$error) {
        header('Refresh: 1; url=trainers.php');
    }
}

$pageTitle = $is_edit ? 'Редактирование тренера' : 'Добавление тренера';
include 'includes/header.php';
?>

<h1><?php echo $is_edit ? 'Редактирование тренера' : 'Добавление тренера'; ?></h1>

<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>
<?php if ($message): ?>
    <div class="alert alert-success"><?php echo $message; ?></div>
<?php endif; ?>

<?php if (!$error || !$message): ?>
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="full_name" class="form-label">ФИО *</label>
                            <input type="text" class="form-control" id="full_name" name="full_name" required 
                                   value="<?php echo htmlspecialchars($trainer['full_name'] ?? $_POST['full_name'] ?? ''); ?>">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="specialization" class="form-label">Специализация *</label>
                                <input type="text" class="form-control" id="specialization" name="specialization" required 
                                       value="<?php echo htmlspecialchars($trainer['specialization'] ?? $_POST['specialization'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="experience" class="form-label">Опыт работы (лет) *</label>
                                <input type="number" class="form-control" id="experience" name="experience" min="0" required 
                                       value="<?php echo $trainer['experience'] ?? $_POST['experience'] ?? ''; ?>">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Телефон</label>
                                <input type="tel" class="form-control" id="phone" name="phone" 
                                       value="<?php echo htmlspecialchars($trainer['phone'] ?? $_POST['phone'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($trainer['email'] ?? $_POST['email'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="schedule" class="form-label">Расписание работы</label>
                            <input type="text" class="form-control" id="schedule" name="schedule" 
                                   value="<?php echo htmlspecialchars($trainer['schedule'] ?? $_POST['schedule'] ?? ''); ?>"
                                   placeholder="Например: Пн-Пт: 10:00-18:00">
                        </div>
                        <div class="mb-3">
                            <label for="bio" class="form-label">Биография</label>
                            <textarea class="form-control" id="bio" name="bio" rows="3"><?php echo htmlspecialchars($trainer['bio'] ?? $_POST['bio'] ?? ''); ?></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Сохранить</button>
                        <a href="trainers.php" class="btn btn-secondary">Отмена</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>

