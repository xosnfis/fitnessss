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

<?php include 'includes/footer.php'; ?>


