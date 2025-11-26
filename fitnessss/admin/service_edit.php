<?php
require_once '../config/config.php';
requireAdmin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$is_edit = $id > 0;

$error = '';
$message = '';

$pdo = getDBConnection();

// Загрузка данных услуги
$service = null;
if ($is_edit) {
    $stmt = $pdo->prepare("SELECT * FROM services WHERE id = ?");
    $stmt->execute([$id]);
    $service = $stmt->fetch();
    if (!$service) {
        $error = 'Услуга не найдена';
    }
}

// Получение тренеров для выпадающего списка
$stmt = $pdo->query("SELECT id, full_name FROM trainers ORDER BY full_name");
$trainers = $stmt->fetchAll();

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $duration = (int)($_POST['duration'] ?? 0);
    $category = sanitize($_POST['category'] ?? '');
    $trainer_id = !empty($_POST['trainer_id']) ? (int)$_POST['trainer_id'] : null;
    $schedule = sanitize($_POST['schedule'] ?? '');
    $max_participants = (int)($_POST['max_participants'] ?? 1);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    if (empty($name) || empty($category) || $price <= 0 || $duration <= 0) {
        $error = 'Заполните все обязательные поля';
    } else {
        try {
            // Проверяем, не существует ли уже услуга с таким именем (кроме текущей при редактировании)
            $check_stmt = $pdo->prepare("SELECT id FROM services WHERE name = ?" . ($is_edit ? " AND id != ?" : ""));
            if ($is_edit) {
                $check_stmt->execute([$name, $id]);
            } else {
                $check_stmt->execute([$name]);
            }
            if ($check_stmt->fetch()) {
                $error = 'Услуга с таким названием уже существует';
            } else {
                if ($is_edit) {
                    $stmt = $pdo->prepare("UPDATE services SET name = ?, description = ?, price = ?, duration = ?, category = ?, trainer_id = ?, schedule = ?, max_participants = ?, is_active = ? WHERE id = ?");
                    $stmt->execute([$name, $description, $price, $duration, $category, $trainer_id, $schedule, $max_participants, $is_active, $id]);
                    $message = 'Услуга успешно обновлена';
                } else {
                    // Случайный рейтинг от 1 до 5 для новой услуги
                    $rating = rand(1, 5);
                    $stmt = $pdo->prepare("INSERT INTO services (name, description, price, duration, category, trainer_id, schedule, max_participants, is_active, rating) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$name, $description, $price, $duration, $category, $trainer_id, $schedule, $max_participants, $is_active, $rating]);
                    $message = 'Услуга успешно создана';
                    $is_edit = true;
                    $id = $pdo->lastInsertId();
                }
            }
        } catch (PDOException $e) {
            // Проверяем, не ошибка ли это дубликата уникального ключа
            if ($e->getCode() == 23000 || strpos($e->getMessage(), 'Duplicate entry') !== false) {
                $error = 'Услуга с таким названием уже существует';
            } else {
                $error = 'Ошибка при сохранении услуги';
            }
            error_log("Service edit error: " . $e->getMessage());
        }
    }
    
    if ($message && !$error) {
        header('Refresh: 1; url=services.php');
    }
}

$pageTitle = $is_edit ? 'Редактирование услуги' : 'Добавление услуги';
include 'includes/header.php';
?>

<h1><?php echo $is_edit ? 'Редактирование услуги' : 'Добавление услуги'; ?></h1>

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
                            <label for="name" class="form-label">Название *</label>
                            <input type="text" class="form-control" id="name" name="name" required 
                                   value="<?php echo htmlspecialchars($service['name'] ?? $_POST['name'] ?? ''); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Описание</label>
                            <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($service['description'] ?? $_POST['description'] ?? ''); ?></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="price" class="form-label">Цена (₽) *</label>
                                <input type="number" class="form-control" id="price" name="price" step="0.01" min="0" required 
                                       value="<?php echo $service['price'] ?? $_POST['price'] ?? ''; ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="duration" class="form-label">Длительность (мин) *</label>
                                <input type="number" class="form-control" id="duration" name="duration" min="1" required 
                                       value="<?php echo $service['duration'] ?? $_POST['duration'] ?? ''; ?>">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="category" class="form-label">Категория *</label>
                            <input type="text" class="form-control" id="category" name="category" required 
                                   value="<?php echo htmlspecialchars($service['category'] ?? $_POST['category'] ?? ''); ?>"
                                   placeholder="Например: Групповые занятия, Персональные тренировки">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="trainer_id" class="form-label">Тренер</label>
                                <select class="form-select" id="trainer_id" name="trainer_id">
                                    <option value="">Не выбран</option>
                                    <?php foreach ($trainers as $trainer): ?>
                                        <option value="<?php echo $trainer['id']; ?>" 
                                                <?php echo ($service['trainer_id'] ?? $_POST['trainer_id'] ?? '') == $trainer['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($trainer['full_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="max_participants" class="form-label">Макс. участников</label>
                                <input type="number" class="form-control" id="max_participants" name="max_participants" min="1" 
                                       value="<?php echo $service['max_participants'] ?? $_POST['max_participants'] ?? 1; ?>">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="schedule" class="form-label">Расписание</label>
                            <input type="text" class="form-control" id="schedule" name="schedule" 
                                   value="<?php echo htmlspecialchars($service['schedule'] ?? $_POST['schedule'] ?? ''); ?>"
                                   placeholder="Например: Пн, Ср, Пт 18:00-19:00">
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                       <?php echo ($service['is_active'] ?? 1) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="is_active">
                                    Активна
                                </label>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Сохранить</button>
                        <a href="services.php" class="btn btn-secondary">Отмена</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>

