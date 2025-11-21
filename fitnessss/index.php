<?php
require_once 'config/config.php';
$pageTitle = 'Главная';
include 'includes/header.php';
?>

<div class="jumbotron bg-primary text-white p-5 rounded mb-4">
    <h1 class="display-4">Добро пожаловать в Фитнес-центр!</h1>
    <p class="lead">Ваш путь к здоровому образу жизни начинается здесь</p>
    <hr class="my-4">
    <p>Профессиональные тренеры, современное оборудование и индивидуальный подход</p>
    <a class="btn btn-light btn-lg" href="services.php" role="button">Посмотреть услуги</a>
</div>

<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-body text-center">
                <h3 class="card-title">💪 Тренажерный зал</h3>
                <p class="card-text">Современное оборудование от ведущих производителей</p>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-body text-center">
                <h3 class="card-title">👥 Групповые занятия</h3>
                <p class="card-text">Йога, пилатес, функциональный тренинг и многое другое</p>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-body text-center">
                <h3 class="card-title">🏃 Персональные тренировки</h3>
                <p class="card-text">Индивидуальный подход к каждому клиенту</p>
            </div>
        </div>
    </div>
</div>

<h2 class="mt-5 mb-4">Почему выбирают нас?</h2>
<ul class="list-group mb-4">
    <li class="list-group-item">✅ Профессиональные тренеры с многолетним опытом</li>
    <li class="list-group-item">✅ Современное оборудование и просторные залы</li>
    <li class="list-group-item">✅ Гибкая система абонементов и доступные цены</li>
    <li class="list-group-item">✅ Индивидуальный подход к каждому клиенту</li>
    <li class="list-group-item">✅ Удобное расположение и график работы</li>
</ul>

<?php include 'includes/footer.php'; ?>

