<?php
require_once 'config/config.php';
$pageTitle = 'Тренеры';
include 'includes/header.php';

try {
    $pdo = getDBConnection();
    $stmt = $pdo->query("SELECT * FROM trainers ORDER BY full_name");
    $trainers = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Trainers error: " . $e->getMessage());
    $trainers = [];
}
?>

<h1>Наши тренеры</h1>

<div class="row">
    <?php if (empty($trainers)): ?>
        <div class="col-12">
            <div class="alert alert-info">Тренеры не найдены</div>
        </div>
    <?php else: ?>
        <?php foreach ($trainers as $trainer): ?>
            <div class="col-md-4 mb-4">
                <div class="card trainer-card h-100">
                    <?php if ($trainer['photo']): ?>
                        <img src="<?php echo htmlspecialchars($trainer['photo']); ?>" class="trainer-photo" alt="<?php echo htmlspecialchars($trainer['full_name']); ?>">
                    <?php else: ?>
                        <div class="trainer-photo bg-secondary d-flex align-items-center justify-content-center text-white">
                            <h3><?php echo mb_substr($trainer['full_name'], 0, 2); ?></h3>
                        </div>
                    <?php endif; ?>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($trainer['full_name']); ?></h5>
                        <p class="card-text">
                            <strong>Специализация:</strong> <?php echo htmlspecialchars($trainer['specialization']); ?><br>
                            <strong>Опыт:</strong> <?php echo $trainer['experience']; ?> лет<br>
                            <?php if ($trainer['schedule']): ?>
                                <strong>Расписание:</strong> <?php echo htmlspecialchars($trainer['schedule']); ?><br>
                            <?php endif; ?>
                        </p>
                        <?php if ($trainer['bio']): ?>
                            <p class="card-text"><small class="text-muted"><?php echo htmlspecialchars($trainer['bio']); ?></small></p>
                        <?php endif; ?>
                        <?php if ($trainer['phone']): ?>
                            <p class="card-text"><small>📞 <?php echo htmlspecialchars($trainer['phone']); ?></small></p>
                        <?php endif; ?>
                        <a href="services.php?trainer_id=<?php echo $trainer['id']; ?>" class="btn btn-primary">Посмотреть услуги</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>

