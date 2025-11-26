<?php
require_once '../config/config.php';
requireAdmin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$is_edit = $id > 0;

$error = '';
$message = '';

$pdo = getDBConnection();

// Загрузка данных абонемента
$subscription = null;
if ($is_edit) {
    $stmt = $pdo->prepare("SELECT * FROM subscriptions WHERE id = ?");
    $stmt->execute([$id]);
    $subscription = $stmt->fetch();
    if (!$subscription) {
        $error = 'Абонемент не найден';
    }
}

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $duration_days = (int)($_POST['duration_days'] ?? 0);
    $visits_count = !empty($_POST['visits_count']) ? (int)$_POST['visits_count'] : null;
    $services_included = sanitize($_POST['services_included'] ?? '');
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    if (empty($name) || $price <= 0 || $duration_days <= 0) {
        $error = 'Заполните все обязательные поля';
    } else {
        try {
            // Проверяем, не существует ли уже абонемент с таким именем (кроме текущего при редактировании)
            $check_stmt = $pdo->prepare("SELECT id FROM subscriptions WHERE name = ?" . ($is_edit ? " AND id != ?" : ""));
            if ($is_edit) {
                $check_stmt->execute([$name, $id]);
            } else {
                $check_stmt->execute([$name]);
            }
            if ($check_stmt->fetch()) {
                $error = 'Абонемент с таким названием уже существует';
            } else {
                if ($is_edit) {
                    $stmt = $pdo->prepare("UPDATE subscriptions SET name = ?, description = ?, price = ?, duration_days = ?, visits_count = ?, services_included = ?, is_active = ? WHERE id = ?");
                    $stmt->execute([$name, $description, $price, $duration_days, $visits_count, $services_included, $is_active, $id]);
                    $message = 'Абонемент успешно обновлен';
                } else {
                    // Случайный рейтинг от 1 до 5 для нового абонемента
                    $rating = rand(1, 5);
                    $stmt = $pdo->prepare("INSERT INTO subscriptions (name, description, price, duration_days, visits_count, services_included, is_active, rating) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$name, $description, $price, $duration_days, $visits_count, $services_included, $is_active, $rating]);
                    $message = 'Абонемент успешно создан';
                    $is_edit = true;
                    $id = $pdo->lastInsertId();
                }
            }
        } catch (PDOException $e) {
            // Проверяем, не ошибка ли это дубликата уникального ключа
            if ($e->getCode() == 23000 || strpos($e->getMessage(), 'Duplicate entry') !== false) {
                $error = 'Абонемент с таким названием уже существует';
            } else {
                $error = 'Ошибка при сохранении абонемента';
            }
            error_log("Subscription edit error: " . $e->getMessage());
        }
    }
    
    if ($message && !$error) {
        header('Refresh: 1; url=subscriptions.php');
    }
}

$pageTitle = $is_edit ? 'Редактирование абонемента' : 'Добавление абонемента';
include 'includes/header.php';
?>

<h1><?php echo $is_edit ? 'Редактирование абонемента' : 'Добавление абонемента'; ?></h1>

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
                                   value="<?php echo htmlspecialchars($subscription['name'] ?? $_POST['name'] ?? ''); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Описание</label>
                            <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($subscription['description'] ?? $_POST['description'] ?? ''); ?></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="price" class="form-label">Цена (₽) *</label>
                                <input type="number" class="form-control" id="price" name="price" step="0.01" min="0" required 
                                       value="<?php echo $subscription['price'] ?? $_POST['price'] ?? ''; ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="duration_days" class="form-label">Срок действия (дней) *</label>
                                <input type="number" class="form-control" id="duration_days" name="duration_days" min="1" required 
                                       value="<?php echo $subscription['duration_days'] ?? $_POST['duration_days'] ?? ''; ?>">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="visits_count" class="form-label">Количество посещений (оставьте пустым для безлимита)</label>
                            <input type="number" class="form-control" id="visits_count" name="visits_count" min="1" 
                                   value="<?php echo $subscription['visits_count'] ?? $_POST['visits_count'] ?? ''; ?>">
                        </div>
                        <div class="mb-3">
                            <label for="services_included" class="form-label">Включенные услуги</label>
                            <input type="text" class="form-control" id="services_included" name="services_included" 
                                   value="<?php echo htmlspecialchars($subscription['services_included'] ?? $_POST['services_included'] ?? ''); ?>"
                                   placeholder="Например: Тренажерный зал, Групповые занятия">
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                       <?php echo ($subscription['is_active'] ?? 1) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="is_active">
                                    Активен
                                </label>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Сохранить</button>
                        <a href="subscriptions.php" class="btn btn-secondary">Отмена</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>

