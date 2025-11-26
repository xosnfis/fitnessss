<?php
require_once 'config/config.php';
requireLogin();
$pageTitle = 'Профиль';
include 'includes/header.php';

$error = '';
$success = '';

// Обработка формы редактирования
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? sanitize($_POST['email']) : '';
    $full_name = isset($_POST['full_name']) ? sanitize($_POST['full_name']) : '';
    $phone = isset($_POST['phone']) ? sanitize($_POST['phone']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $password_confirm = isset($_POST['password_confirm']) ? $_POST['password_confirm'] : '';
    
    // Валидация
    if (empty($email) || empty($full_name)) {
        $error = 'Email и полное имя обязательны для заполнения';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Некорректный email адрес';
    } elseif (!empty($password) && $password !== $password_confirm) {
        $error = 'Пароли не совпадают';
    } elseif (!empty($password) && strlen($password) < 6) {
        $error = 'Пароль должен содержать минимум 6 символов';
    } else {
        try {
            $pdo = getDBConnection();
            $pdo->beginTransaction();
            
            // Проверяем, не занят ли email другим пользователем
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $_SESSION['user_id']]);
            if ($stmt->fetch()) {
                $error = 'Этот email уже используется другим пользователем';
                $pdo->rollBack();
            } else {
                // Обновляем данные пользователя
                if (!empty($password)) {
                    // Обновляем с паролем
                    $password_hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET email = ?, full_name = ?, phone = ?, password = ? WHERE id = ?");
                    $stmt->execute([$email, $full_name, $phone, $password_hash, $_SESSION['user_id']]);
                } else {
                    // Обновляем без пароля
                    $stmt = $pdo->prepare("UPDATE users SET email = ?, full_name = ?, phone = ? WHERE id = ?");
                    $stmt->execute([$email, $full_name, $phone, $_SESSION['user_id']]);
                }
                
                $pdo->commit();
                
                // Обновляем данные в сессии
                $_SESSION['email'] = $email;
                $_SESSION['full_name'] = $full_name;
                
                $success = 'Данные успешно обновлены!';
            }
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = 'Ошибка при обновлении данных. Попробуйте позже.';
            error_log("Profile update error: " . $e->getMessage());
        }
    }
}

// Получаем данные пользователя
try {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
} catch (PDOException $e) {
    error_log("Profile error: " . $e->getMessage());
    $user = null;
}
?>

<h1 class="mb-4 fade-in-on-scroll">
    <i class="fas fa-user-circle text-primary me-2"></i>Мой профиль
</h1>

<?php if ($error): ?>
    <div class="alert alert-danger fade-in-on-scroll">
        <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
    </div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert alert-success fade-in-on-scroll">
        <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
    </div>
<?php endif; ?>

<?php if ($user): ?>
    <div class="row">
        <div class="col-md-8">
            <div class="card fade-in-on-scroll">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-edit me-2"></i>Редактирование профиля
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="username" class="form-label">Логин</label>
                            <input type="text" class="form-control" id="username" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                            <small class="text-muted">Логин нельзя изменить</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email *</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="full_name" class="form-label">Полное имя *</label>
                            <input type="text" class="form-control" id="full_name" name="full_name" 
                                   value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="phone" class="form-label">Телефон</label>
                            <input type="tel" class="form-control" id="phone" name="phone" 
                                   value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" 
                                   placeholder="+7 (999) 123-45-67">
                        </div>
                        
                        <div class="mb-3">
                            <label for="role" class="form-label">Роль</label>
                            <input type="text" class="form-control" id="role" 
                                   value="<?php echo $user['role'] === 'admin' ? 'Администратор' : 'Пользователь'; ?>" disabled>
                            <small class="text-muted">Роль нельзя изменить</small>
                        </div>
                        
                        <hr class="my-4">
                        
                        <h6 class="mb-3">Изменить пароль (оставьте пустым, если не хотите менять)</h6>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Новый пароль</label>
                            <input type="password" class="form-control" id="password" name="password" 
                                   placeholder="Минимум 6 символов">
                            <small class="text-muted">Оставьте пустым, если не хотите менять пароль</small>
                        </div>
                        
                        <div class="mb-4">
                            <label for="password_confirm" class="form-label">Подтвердите новый пароль</label>
                            <input type="password" class="form-control" id="password_confirm" name="password_confirm" 
                                   placeholder="Повторите пароль">
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Сохранить изменения
                            </button>
                            <a href="profile.php" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Отмена
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card fade-in-on-scroll">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>Информация
                    </h5>
                </div>
                <div class="card-body">
                    <p class="mb-2">
                        <strong>Дата регистрации:</strong><br>
                        <i class="fas fa-calendar me-1"></i>
                        <?php echo date('d.m.Y H:i', strtotime($user['created_at'])); ?>
                    </p>
                    <p class="mb-0">
                        <strong>Последнее обновление:</strong><br>
                        <i class="fas fa-clock me-1"></i>
                        <?php echo date('d.m.Y H:i', strtotime($user['updated_at'])); ?>
                    </p>
                </div>
            </div>
            
            <?php
            // Показываем активные подписки пользователя
            try {
                $stmt = $pdo->prepare("SELECT us.*, s.name as subscription_name, s.price as subscription_price, s.visits_count 
                                      FROM user_subscriptions us 
                                      INNER JOIN subscriptions s ON us.subscription_id = s.id 
                                      WHERE us.user_id = ? AND us.is_active = 1 AND us.end_date >= CURDATE() 
                                      ORDER BY us.end_date DESC");
                $stmt->execute([$_SESSION['user_id']]);
                $active_subscriptions = $stmt->fetchAll();
                
                if (!empty($active_subscriptions)):
            ?>
                <div class="card fade-in-on-scroll mt-3">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-id-card me-2"></i>Активные подписки
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php foreach ($active_subscriptions as $sub): ?>
                            <div class="mb-3 pb-3 border-bottom">
                                <h6 class="mb-1"><?php echo htmlspecialchars($sub['subscription_name']); ?></h6>
                                <p class="mb-1 text-muted small">
                                    <i class="fas fa-calendar-check me-1"></i>
                                    Действует до: <?php echo date('d.m.Y', strtotime($sub['end_date'])); ?>
                                </p>
                                <?php if ($sub['visits_count']): ?>
                                    <p class="mb-1 text-muted small">
                                        <i class="fas fa-ticket-alt me-1"></i>
                                        Использовано посещений: <?php echo $sub['visits_used']; ?> / <?php echo $sub['visits_count']; ?>
                                    </p>
                                <?php endif; ?>
                                <div class="mt-2">
                                    <button class="btn btn-danger btn-sm" 
                                            onclick="cancelSubscription(<?php echo $sub['id']; ?>, '<?php echo htmlspecialchars($sub['subscription_name'], ENT_QUOTES); ?>')">
                                        <i class="fas fa-times me-1"></i>Отменить абонемент
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php
                endif;
            } catch (PDOException $e) {
                error_log("Subscriptions fetch error: " . $e->getMessage());
            }
            ?>
        </div>
    </div>
<?php else: ?>
    <div class="alert alert-danger fade-in-on-scroll">
        <i class="fas fa-exclamation-circle me-2"></i>Ошибка загрузки профиля
    </div>
<?php endif; ?>

<script>
function cancelSubscription(subscriptionId, subscriptionName) {
    // Подтверждение отмены
    if (!confirm('Вы уверены, что хотите отменить абонемент "' + subscriptionName + '"?\n\nПосле отмены вы сможете приобрести новый абонемент.')) {
        return;
    }
    
    // Создаем форму для отправки POST запроса
    const formData = new FormData();
    formData.append('subscription_id', subscriptionId);
    
    // Показываем индикатор загрузки
    const button = event.target.closest('button');
    const originalText = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Отмена...';
    
    // Отправляем запрос
    fetch('api/cancel_subscription.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Показываем уведомление об успехе
            showNotification('Абонемент успешно отменен', 'success');
            
            // Перезагружаем страницу через небольшую задержку
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            // Показываем ошибку
            showNotification(data.message || 'Ошибка при отмене абонемента', 'error');
            button.disabled = false;
            button.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Произошла ошибка при отмене абонемента', 'error');
        button.disabled = false;
        button.innerHTML = originalText;
    });
}

// Функция для показа уведомлений (если не определена в main.js)
function showNotification(message, type = 'info') {
    // Проверяем, есть ли функция showNotification из main.js
    if (typeof window.showNotification === 'function') {
        window.showNotification(message, type);
        return;
    }
    
    // Если нет, создаем простое уведомление
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'info'} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    document.body.appendChild(notification);
    
    // Автоматически удаляем через 5 секунд
    setTimeout(() => {
        notification.remove();
    }, 5000);
}
</script>

<?php include 'includes/footer.php'; ?>
