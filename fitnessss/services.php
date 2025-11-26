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
    
    // Устанавливаем случайный рейтинг для записей без рейтинга
    foreach ($services as &$service) {
        if (!isset($service['rating']) || empty($service['rating']) || $service['rating'] == 0) {
            $rating = rand(1, 5);
            $service['rating'] = $rating;
            // Сохраняем рейтинг в БД (если поле существует)
            try {
                $update_stmt = $pdo->prepare("UPDATE services SET rating = ? WHERE id = ?");
                $update_stmt->execute([$rating, $service['id']]);
            } catch (PDOException $e) {
                // Поле rating может еще не существовать - это нормально
                error_log("Rating field might not exist: " . $e->getMessage());
            }
        }
    }
    unset($service);
    
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

<h1 class="mb-4 fade-in-on-scroll">
    <i class="fas fa-dumbbell text-primary me-2"></i>Услуги
</h1>

<div class="card mb-5 fade-in-on-scroll filter-card">
    <div class="card-body p-4">
        <h5 class="mb-4">
            <i class="fas fa-filter me-2"></i>Фильтры
        </h5>
        <form method="GET" action="" class="row g-3">
            <div class="col-md-4">
                <label for="category" class="form-label">
                    <i class="fas fa-tags me-1"></i>Категория
                </label>
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
                <label for="trainer_id" class="form-label">
                    <i class="fas fa-user-tie me-1"></i>Тренер
                </label>
                <select class="form-select" id="trainer_id" name="trainer_id">
                    <option value="">Все тренеры</option>
                    <?php foreach ($trainers as $trainer): ?>
                        <option value="<?php echo $trainer['id']; ?>" <?php echo $trainer_id == $trainer['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($trainer['full_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <div class="w-100">
                    <button type="submit" class="btn btn-primary w-100 mb-2">
                        <i class="fas fa-check me-2"></i>Применить
                    </button>
                    <a href="services.php" class="btn btn-secondary w-100">
                        <i class="fas fa-redo me-2"></i>Сбросить
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="row">
    <?php if (empty($services)): ?>
        <div class="col-12">
            <div class="alert alert-info fade-in-on-scroll">
                <i class="fas fa-info-circle me-2"></i>Услуги не найдены
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($services as $index => $service): ?>
            <div class="col-md-6 col-lg-4 mb-4 fade-in-on-scroll" style="animation-delay: <?php echo ($index % 3) * 0.1; ?>s;">
                <div class="card service-card h-100">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">
                            <i class="fas fa-dumbbell text-primary me-2"></i>
                            <?php echo htmlspecialchars($service['name']); ?>
                        </h5>
                        <!-- Отображение звезд рейтинга -->
                        <?php 
                        $rating = isset($service['rating']) && $service['rating'] > 0 ? (int)$service['rating'] : 0;
                        if ($rating > 0):
                        ?>
                        <div class="mb-2 rating-stars">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <?php if ($i <= $rating): ?>
                                    <span class="star star-filled">⭐</span>
                                <?php else: ?>
                                    <span class="star star-empty">☆</span>
                                <?php endif; ?>
                            <?php endfor; ?>
                        </div>
                        <?php endif; ?>
                        <p class="card-text flex-grow-1"><?php echo htmlspecialchars($service['description']); ?></p>
                        <div class="mb-3">
                            <p class="mb-2">
                                <small class="text-muted">
                                    <i class="fas fa-tag me-1"></i>Категория: <strong><?php echo htmlspecialchars($service['category']); ?></strong>
                                </small>
                            </p>
                            <?php if ($service['trainer_name']): ?>
                                <p class="mb-2">
                                    <small class="text-muted">
                                        <i class="fas fa-user-tie me-1"></i>Тренер: <strong><?php echo htmlspecialchars($service['trainer_name']); ?></strong>
                                    </small>
                                </p>
                            <?php endif; ?>
                            <p class="mb-2">
                                <small class="text-muted">
                                    <i class="fas fa-clock me-1"></i>Длительность: <?php echo $service['duration']; ?> мин.
                                </small>
                            </p>
                            <?php if ($service['schedule']): ?>
                                <p class="mb-0">
                                    <small class="text-muted">
                                        <i class="fas fa-calendar-alt me-1"></i>Расписание: <?php echo htmlspecialchars($service['schedule']); ?>
                                    </small>
                                </p>
                            <?php endif; ?>
                        </div>
                        <div class="price mb-3"><?php echo number_format($service['price'], 2, '.', ' '); ?> ₽</div>
                        <?php if (isLoggedIn()): ?>
                            <button class="btn btn-primary w-100" onclick="addToCart({
                                type: 'service',
                                id: <?php echo $service['id']; ?>,
                                name: '<?php echo htmlspecialchars($service['name'], ENT_QUOTES); ?>',
                                price: <?php echo $service['price']; ?>,
                                quantity: 1
                            })">
                                <i class="fas fa-cart-plus me-2"></i>Добавить в корзину
                            </button>
                        <?php else: ?>
                            <a href="login.php" class="btn btn-secondary w-100">
                                <i class="fas fa-sign-in-alt me-2"></i>Войдите для покупки
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<style>
.rating-stars {
    display: flex;
    gap: 2px;
    align-items: center;
}

.rating-stars .star {
    font-size: 20px;
    line-height: 1;
    display: inline-block;
    transition: transform 0.2s ease;
    user-select: none;
}

.rating-stars .star:hover {
    transform: scale(1.2);
}

.rating-stars .star-filled {
    color: #FFD700; /* Золотой цвет для заполненных звезд */
    filter: drop-shadow(0 0 2px rgba(255, 215, 0, 0.5));
}

.rating-stars .star-empty {
    color: #CCCCCC; /* Серый цвет для пустых звезд */
    opacity: 0.4;
}
</style>

<?php include 'includes/footer.php'; ?>

