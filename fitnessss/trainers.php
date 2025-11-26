<?php
require_once 'config/config.php';
$pageTitle = 'Тренеры';
include 'includes/header.php';

try {
    $pdo = getDBConnection();
    $stmt = $pdo->query("SELECT * FROM trainers ORDER BY full_name");
    $trainers = $stmt->fetchAll();
    
    // Загружаем фотографии для каждого тренера
    foreach ($trainers as &$trainer) {
        $trainer['photos'] = [];
        
        // Пытаемся получить фотографии из новой таблицы
        try {
            $photos_stmt = $pdo->prepare("SELECT * FROM trainer_photos WHERE trainer_id = ? ORDER BY display_order ASC, id ASC");
            $photos_stmt->execute([$trainer['id']]);
            $trainer['photos'] = $photos_stmt->fetchAll();
        } catch (PDOException $e) {
            // Таблица может еще не существовать - это нормально
            error_log("Trainer photos table might not exist: " . $e->getMessage());
        }
        
        // Если нет фотографий в новой таблице, используем старое поле photo для обратной совместимости
        if (empty($trainer['photos']) && !empty($trainer['photo'])) {
            $trainer['photos'] = [['photo_path' => $trainer['photo'], 'id' => 0]];
        }
    }
    unset($trainer);
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
                    <?php if (!empty($trainer['photos'])): ?>
                        <!-- Слайдер фотографий -->
                        <div id="trainerCarousel<?php echo $trainer['id']; ?>" class="carousel slide trainer-photo-carousel" data-bs-ride="carousel">
                            <div class="carousel-inner">
                                <?php foreach ($trainer['photos'] as $index => $photo): ?>
                                    <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                                        <img src="<?php echo htmlspecialchars($photo['photo_path']); ?>" 
                                             class="trainer-photo d-block w-100" 
                                             alt="<?php echo htmlspecialchars($trainer['full_name']); ?>">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <?php if (count($trainer['photos']) > 1): ?>
                                <button class="carousel-control-prev" type="button" data-bs-target="#trainerCarousel<?php echo $trainer['id']; ?>" data-bs-slide="prev">
                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Предыдущее</span>
                                </button>
                                <button class="carousel-control-next" type="button" data-bs-target="#trainerCarousel<?php echo $trainer['id']; ?>" data-bs-slide="next">
                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Следующее</span>
                                </button>
                                <!-- Индикаторы -->
                                <div class="carousel-indicators">
                                    <?php foreach ($trainer['photos'] as $index => $photo): ?>
                                        <button type="button" 
                                                data-bs-target="#trainerCarousel<?php echo $trainer['id']; ?>" 
                                                data-bs-slide-to="<?php echo $index; ?>" 
                                                class="<?php echo $index === 0 ? 'active' : ''; ?>" 
                                                aria-current="<?php echo $index === 0 ? 'true' : 'false'; ?>"
                                                aria-label="Слайд <?php echo $index + 1; ?>"></button>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
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

<style>
.trainer-photo-carousel {
    position: relative;
    height: 300px;
    overflow: hidden;
}

.trainer-photo-carousel .trainer-photo {
    width: 100%;
    height: 300px;
    object-fit: cover;
}

.trainer-photo-carousel .carousel-control-prev,
.trainer-photo-carousel .carousel-control-next {
    background-color: rgba(0, 0, 0, 0.3);
    width: 40px;
    height: 40px;
    border-radius: 50%;
    top: 50%;
    transform: translateY(-50%);
    opacity: 0.7;
    transition: opacity 0.3s;
}

.trainer-photo-carousel:hover .carousel-control-prev,
.trainer-photo-carousel:hover .carousel-control-next {
    opacity: 1;
}

.trainer-photo-carousel .carousel-indicators {
    margin-bottom: 10px;
}

.trainer-photo-carousel .carousel-indicators button {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background-color: rgba(255, 255, 255, 0.5);
    border: 2px solid rgba(255, 255, 255, 0.8);
}

.trainer-photo-carousel .carousel-indicators button.active {
    background-color: #fff;
}
</style>

<?php include 'includes/footer.php'; ?>

