<?php
require_once 'config/config.php';
$pageTitle = 'Услуги';
include 'includes/header.php';

try {
    $pdo = getDBConnection();
    
    // Получение фильтров
    $category = $_GET['category'] ?? '';
    $trainer_id = $_GET['trainer_id'] ?? '';
    
    $sql = "SELECT s.*, t.full_name as trainer_name FROM services s 
            LEFT JOIN trainers t ON s.trainer_id = t.id 
            WHERE s.is_active = 1";
    $params = [];
    
    if ($category) {
        $sql .= " AND s.category = ?";
        $params[] = $category;
    }
    
    if ($trainer_id) {
        $sql .= " AND s.trainer_id = ?";
        $params[] = $trainer_id;
    }
    
    $sql .= " ORDER BY s.name";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $services = $stmt->fetchAll();
    
    // Получение категорий для фильтра
    $stmt = $pdo->query("SELECT DISTINCT category FROM services WHERE is_active = 1 ORDER BY category");
    $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Получение тренеров для фильтра
    $stmt = $pdo->query("SELECT id, full_name FROM trainers ORDER BY full_name");
    $trainers = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Services error: " . $e->getMessage());
    $services = [];
    $categories = [];
    $trainers = [];
}
?>

<h1>Услуги</h1>

<div class="card mb-4">
    <div class="card-body">
        <h5>Фильтры</h5>
        <form method="GET" action="" class="row g-3">
            <div class="col-md-4">
                <label for="category" class="form-label">Категория</label>
                <select class="form-select" id="category" name="category">
                    <option value="">Все категории</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo $category === $cat ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label for="trainer_id" class="form-label">Тренер</label>
                <select class="form-select" id="trainer_id" name="trainer_id">
                    <option value="">Все тренеры</option>
                    <?php foreach ($trainers as $trainer): ?>
                        <option value="<?php echo $trainer['id']; ?>" <?php echo $trainer_id == $trainer['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($trainer['full_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">&nbsp;</label>
                <div>
                    <button type="submit" class="btn btn-primary">Применить</button>
                    <a href="services.php" class="btn btn-secondary">Сбросить</a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="row">
    <?php if (empty($services)): ?>
        <div class="col-12">
            <div class="alert alert-info">Услуги не найдены</div>
        </div>
    <?php else: ?>
        <?php foreach ($services as $service): ?>
            <div class="col-md-4 mb-4">
                <div class="card service-card h-100">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($service['name']); ?></h5>
                        <p class="card-text"><?php echo htmlspecialchars($service['description']); ?></p>
                        <p class="card-text">
                            <small class="text-muted">
                                Категория: <strong><?php echo htmlspecialchars($service['category']); ?></strong><br>
                                <?php if ($service['trainer_name']): ?>
                                    Тренер: <strong><?php echo htmlspecialchars($service['trainer_name']); ?></strong><br>
                                <?php endif; ?>
                                Длительность: <?php echo $service['duration']; ?> мин.<br>
                                <?php if ($service['schedule']): ?>
                                    Расписание: <?php echo htmlspecialchars($service['schedule']); ?><br>
                                <?php endif; ?>
                            </small>
                        </p>
                        <div class="price"><?php echo number_format($service['price'], 2, '.', ' '); ?> ₽</div>
                        <?php if (isLoggedIn()): ?>
                            <button class="btn btn-primary mt-2" onclick="addToCart({
                                type: 'service',
                                id: <?php echo $service['id']; ?>,
                                name: '<?php echo htmlspecialchars($service['name'], ENT_QUOTES); ?>',
                                price: <?php echo $service['price']; ?>,
                                quantity: 1
                            })">Добавить в корзину</button>
                        <?php else: ?>
                            <a href="login.php" class="btn btn-secondary mt-2">Войдите для покупки</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>

