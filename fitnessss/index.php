<?php
require_once 'config/config.php';
$pageTitle = 'Главная';
include 'includes/header.php';
?>

<div class="jumbotron text-white p-5 rounded mb-5 fade-in-on-scroll">
    <h1 class="display-4 fw-bold mb-3">
        <i class="fas fa-fire me-3"></i>Добро пожаловать в Фитнес-центр!
    </h1>
    <p class="lead fs-4 mb-3">Ваш путь к здоровому образу жизни начинается здесь</p>
    <hr class="my-4" style="border-color: rgba(255,255,255,0.3);">
    <p class="fs-5 mb-4">Профессиональные тренеры, современное оборудование и индивидуальный подход к каждому клиенту</p>
    <a class="btn btn-light btn-lg px-4 py-3" href="services.php" role="button">
        <i class="fas fa-dumbbell me-2"></i>Посмотреть услуги
    </a>
</div>

<div class="row mb-5">
    <div class="col-md-4 mb-4 fade-in-on-scroll">
        <div class="card h-100">
            <div class="card-body text-center p-4">
                <div class="mb-3" style="font-size: 3rem;">
                    <i class="fas fa-dumbbell text-primary"></i>
                </div>
                <h3 class="card-title mb-3">Тренажерный зал</h3>
                <p class="card-text">Современное оборудование от ведущих производителей для эффективных тренировок</p>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4 fade-in-on-scroll">
        <div class="card h-100">
            <div class="card-body text-center p-4">
                <div class="mb-3" style="font-size: 3rem;">
                    <i class="fas fa-users text-primary"></i>
                </div>
                <h3 class="card-title mb-3">Групповые занятия</h3>
                <p class="card-text">Йога, пилатес, функциональный тренинг и многое другое в дружественной атмосфере</p>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4 fade-in-on-scroll">
        <div class="card h-100">
            <div class="card-body text-center p-4">
                <div class="mb-3" style="font-size: 3rem;">
                    <i class="fas fa-user-tie text-primary"></i>
                </div>
                <h3 class="card-title mb-3">Персональные тренировки</h3>
                <p class="card-text">Индивидуальный подход к каждому клиенту для достижения максимальных результатов</p>
            </div>
        </div>
    </div>
</div>

<h2 class="mt-5 mb-4 text-center fade-in-on-scroll">
    <i class="fas fa-star text-warning me-2"></i>Почему выбирают нас?
</h2>
<div class="row">
    <div class="col-lg-8 mx-auto">
        <ul class="list-group mb-5 fade-in-on-scroll">
            <li class="list-group-item d-flex align-items-center">
                <i class="fas fa-check-circle text-success me-3 fs-4"></i>
                <span>Профессиональные тренеры с многолетним опытом и сертификатами</span>
            </li>
            <li class="list-group-item d-flex align-items-center">
                <i class="fas fa-check-circle text-success me-3 fs-4"></i>
                <span>Современное оборудование и просторные залы с зонированием</span>
            </li>
            <li class="list-group-item d-flex align-items-center">
                <i class="fas fa-check-circle text-success me-3 fs-4"></i>
                <span>Гибкая система абонементов и доступные цены для всех</span>
            </li>
            <li class="list-group-item d-flex align-items-center">
                <i class="fas fa-check-circle text-success me-3 fs-4"></i>
                <span>Индивидуальный подход к каждому клиенту и составление персональных программ</span>
            </li>
            <li class="list-group-item d-flex align-items-center">
                <i class="fas fa-check-circle text-success me-3 fs-4"></i>
                <span>Удобное расположение в центре города и гибкий график работы</span>
            </li>
        </ul>
    </div>
</div>

<?php
// Получаем активные сертификаты
try {
    $pdo = getDBConnection();
    $stmt = $pdo->query("SELECT * FROM certificates WHERE is_active = 1 ORDER BY display_order ASC, created_at DESC");
    $certificates = $stmt->fetchAll();
    
    if (!empty($certificates)):
?>
    <h2 class="mt-5 mb-4 text-center fade-in-on-scroll">
        <i class="fas fa-certificate text-primary me-2"></i>Наши сертификаты
    </h2>
    <div class="row mb-5 fade-in-on-scroll">
        <?php foreach ($certificates as $cert): ?>
            <div class="col-md-4 col-lg-3 mb-4">
                <div class="card h-100 certificate-card" style="cursor: pointer; transition: transform 0.3s ease;" 
                     onclick="openCertificateModal('<?php echo htmlspecialchars($cert['image_path'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($cert['title'], ENT_QUOTES); ?>')">
                    <div class="card-img-top position-relative" style="height: 250px; overflow: hidden; background: #f8f9fa;">
                        <img src="<?php echo htmlspecialchars($cert['image_path']); ?>" 
                             alt="<?php echo htmlspecialchars($cert['title']); ?>"
                             class="w-100 h-100" 
                             style="object-fit: cover;">
                        <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center" 
                             style="background: rgba(0,0,0,0); transition: background 0.3s ease;">
                            <i class="fas fa-search-plus text-white" style="font-size: 2rem; opacity: 0; transition: opacity 0.3s ease;"></i>
                        </div>
                    </div>
                    <div class="card-body">
                        <h6 class="card-title mb-1"><?php echo htmlspecialchars($cert['title']); ?></h6>
                        <?php if ($cert['description']): ?>
                            <p class="card-text small text-muted mb-0"><?php echo htmlspecialchars(mb_substr($cert['description'], 0, 60)); ?><?php echo mb_strlen($cert['description']) > 60 ? '...' : ''; ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <!-- Модальное окно для просмотра сертификата -->
    <div class="modal fade" id="certificateModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="certificateModalTitle"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center p-0">
                    <img id="certificateModalImg" src="" alt="" class="img-fluid">
                </div>
            </div>
        </div>
    </div>
    
    <style>
        .certificate-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        .certificate-card:hover .card-img-top > div {
            background: rgba(0,0,0,0.5) !important;
        }
        .certificate-card:hover .card-img-top i {
            opacity: 1 !important;
        }
    </style>
    
    <script>
    function openCertificateModal(imagePath, title) {
        document.getElementById('certificateModalImg').src = imagePath;
        document.getElementById('certificateModalTitle').textContent = title;
        const modal = new bootstrap.Modal(document.getElementById('certificateModal'));
        modal.show();
    }
    </script>
<?php
    endif;
} catch (PDOException $e) {
    error_log("Certificates display error: " . $e->getMessage());
}
?>

<?php include 'includes/footer.php'; ?>


