<?php
require_once 'config/config.php';
$pageTitle = 'Абонементы';
include 'includes/header.php';

try {
    $pdo = getDBConnection();
    $stmt = $pdo->query("SELECT * FROM subscriptions WHERE is_active = 1 ORDER BY price");
    $subscriptions = $stmt->fetchAll();
    
    // Устанавливаем случайный рейтинг для записей без рейтинга
    foreach ($subscriptions as &$subscription) {
        if (!isset($subscription['rating']) || empty($subscription['rating']) || $subscription['rating'] == 0) {
            $rating = rand(1, 5);
            $subscription['rating'] = $rating;
            // Сохраняем рейтинг в БД (если поле существует)
            try {
                $update_stmt = $pdo->prepare("UPDATE subscriptions SET rating = ? WHERE id = ?");
                $update_stmt->execute([$rating, $subscription['id']]);
            } catch (PDOException $e) {
                // Поле rating может еще не существовать - это нормально
                error_log("Rating field might not exist: " . $e->getMessage());
            }
        }
    }
    unset($subscription);
    
    // Проверяем наличие активного абонемента у пользователя
    $active_subscription = null;
    if (isLoggedIn()) {
        $stmt_active = $pdo->prepare("SELECT us.*, s.name as subscription_name 
                                      FROM user_subscriptions us
                                      JOIN subscriptions s ON us.subscription_id = s.id
                                      WHERE us.user_id = ? 
                                      AND us.is_active = TRUE 
                                      AND us.end_date >= CURDATE()
                                      LIMIT 1");
        $stmt_active->execute([$_SESSION['user_id']]);
        $active_subscription = $stmt_active->fetch();
    }
} catch (PDOException $e) {
    error_log("Subscriptions error: " . $e->getMessage());
    $subscriptions = [];
    $active_subscription = null;
}
?>

<h1>Абонементы</h1>

<?php if (isLoggedIn() && $active_subscription): ?>
    <div class="alert alert-warning fade-in-on-scroll mb-4">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <strong>У вас уже есть активный абонемент!</strong><br>
        Абонемент: <strong><?php echo htmlspecialchars($active_subscription['subscription_name']); ?></strong><br>
        Действует до: <strong><?php echo date('d.m.Y', strtotime($active_subscription['end_date'])); ?></strong><br>
        <small>Один пользователь может иметь только один активный абонемент. Вы сможете приобрести новый абонемент после окончания текущего.</small>
    </div>
<?php endif; ?>

<div class="row">
    <?php if (empty($subscriptions)): ?>
        <div class="col-12">
            <div class="alert alert-info">Абонементы не найдены</div>
        </div>
    <?php else: ?>
        <?php foreach ($subscriptions as $subscription): ?>
            <div class="col-md-4 mb-4">
                <div class="card subscription-card h-100">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($subscription['name']); ?></h5>
                        <!-- Отображение звезд рейтинга -->
                        <?php 
                        $rating = isset($subscription['rating']) && $subscription['rating'] > 0 ? (int)$subscription['rating'] : 0;
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
                        <p class="card-text"><?php echo htmlspecialchars($subscription['description']); ?></p>
                        <ul class="list-unstyled">
                            <li><strong>Срок действия:</strong> <?php echo $subscription['duration_days']; ?> дней</li>
                            <li><strong>Посещений:</strong> 
                                <?php echo $subscription['visits_count'] ? $subscription['visits_count'] : 'Безлимит'; ?>
                            </li>
                            <?php if ($subscription['services_included']): ?>
                                <li><strong>Включено:</strong> <?php echo htmlspecialchars($subscription['services_included']); ?></li>
                            <?php endif; ?>
                        </ul>
                        <div class="price"><?php echo number_format($subscription['price'], 2, '.', ' '); ?> ₽</div>
                        <?php if (isLoggedIn()): ?>
                            <?php if ($active_subscription): ?>
                                <button class="btn btn-secondary mt-2" disabled title="У вас уже есть активный абонемент">
                                    <i class="fas fa-lock me-2"></i>Недоступно
                                </button>
                            <?php else: ?>
                                <button class="btn btn-primary mt-2" onclick="addToCart({
                                    type: 'subscription',
                                    id: <?php echo $subscription['id']; ?>,
                                    name: '<?php echo htmlspecialchars($subscription['name'], ENT_QUOTES); ?>',
                                    price: <?php echo $subscription['price']; ?>,
                                    quantity: 1
                                })">Добавить в корзину</button>
                            <?php endif; ?>
                        <?php else: ?>
                            <a href="login.php" class="btn btn-secondary mt-2">Войдите для покупки</a>
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

