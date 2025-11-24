<!DOCTYPE html>
<html lang="ru" class="smooth-scroll">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Современный фитнес-центр с профессиональными тренерами, услугами и абонементами">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>Фитнес-центр</title>
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* Предотвращение мерцания при загрузке */
        body {
            visibility: hidden;
        }
        body.loaded {
            visibility: visible;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-dumbbell"></i> Фитнес-центр
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Переключить навигацию">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php"><i class="fas fa-home me-1"></i>Главная</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="services.php"><i class="fas fa-dumbbell me-1"></i>Услуги</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="trainers.php"><i class="fas fa-users me-1"></i>Тренеры</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="subscriptions.php"><i class="fas fa-id-card me-1"></i>Абонементы</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="faq.php"><i class="fas fa-question-circle me-1"></i>FAQ</a>
                    </li>
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="cart.php" data-cart-count>
                                <i class="fas fa-shopping-cart me-1"></i>Корзина
                                <span id="cart-count" class="badge bg-primary ms-1" style="display: none;">0</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="orders.php"><i class="fas fa-list-alt me-1"></i>Мои заказы</a>
                        </li>
                        <?php if (isAdmin()): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="admin/index.php"><i class="fas fa-cog me-1"></i>Админ-панель</a>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav">
                    <?php if (isLoggedIn()): ?>
                        <?php
                        // Получаем актуальные данные пользователя из БД (включая username)
                        $current_username = $_SESSION['username'] ?? '';
                        $active_subscription = null;
                        try {
                            $pdo = getDBConnection();
                            // Получаем актуальный username из БД
                            $stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
                            $stmt->execute([$_SESSION['user_id']]);
                            $user_data = $stmt->fetch();
                            if ($user_data) {
                                $current_username = $user_data['username'];
                                // Обновляем сессию, если username изменился
                                if ($_SESSION['username'] !== $current_username) {
                                    $_SESSION['username'] = $current_username;
                                }
                            }
                            
                            // Получаем активную подписку пользователя
                            $stmt = $pdo->prepare("SELECT us.*, s.name as subscription_name 
                                                  FROM user_subscriptions us 
                                                  INNER JOIN subscriptions s ON us.subscription_id = s.id 
                                                  WHERE us.user_id = ? AND us.is_active = 1 AND us.end_date >= CURDATE() 
                                                  ORDER BY us.end_date DESC 
                                                  LIMIT 1");
                            $stmt->execute([$_SESSION['user_id']]);
                            $active_subscription = $stmt->fetch();
                        } catch (PDOException $e) {
                            error_log("User data fetch error: " . $e->getMessage());
                        }
                        ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($current_username); ?>
                                <?php if ($active_subscription): ?>
                                    <span class="badge bg-success ms-2" title="Активная подписка: <?php echo htmlspecialchars($active_subscription['subscription_name']); ?> до <?php echo date('d.m.Y', strtotime($active_subscription['end_date'])); ?>">
                                        <i class="fas fa-id-card me-1"></i><?php echo htmlspecialchars($active_subscription['subscription_name']); ?>
                                    </span>
                                <?php endif; ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user-circle me-2"></i>Профиль</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Выйти</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php"><i class="fas fa-sign-in-alt me-1"></i>Вход</a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-register ms-2" href="register.php">
                                <i class="fas fa-user-plus me-1"></i>Регистрация
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    <main class="container my-5 flex-grow-1">

