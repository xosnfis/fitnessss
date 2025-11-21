<?php
require_once 'config/config.php';
$pageTitle = 'Абонементы';
include 'includes/header.php';

try {
    $pdo = getDBConnection();
    $stmt = $pdo->query("SELECT * FROM subscriptions WHERE is_active = 1 ORDER BY price");
    $subscriptions = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Subscriptions error: " . $e->getMessage());
    $subscriptions = [];
}
?>

<h1>Абонементы</h1>

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
                            <button class="btn btn-primary mt-2" onclick="addToCart({
                                type: 'subscription',
                                id: <?php echo $subscription['id']; ?>,
                                name: '<?php echo htmlspecialchars($subscription['name'], ENT_QUOTES); ?>',
                                price: <?php echo $subscription['price']; ?>,
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

