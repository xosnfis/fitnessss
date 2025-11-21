<?php
require_once 'config/config.php';
requireLogin();
$pageTitle = 'Профиль';
include 'includes/header.php';

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

<h1>Мой профиль</h1>

<?php if ($user): ?>
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Информация о пользователе</h5>
            <p><strong>Логин:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
            <p><strong>Полное имя:</strong> <?php echo htmlspecialchars($user['full_name']); ?></p>
            <p><strong>Телефон:</strong> <?php echo htmlspecialchars($user['phone'] ?? '-'); ?></p>
            <p><strong>Роль:</strong> 
                <span class="badge <?php echo $user['role'] === 'admin' ? 'bg-danger' : 'bg-primary'; ?>">
                    <?php echo $user['role'] === 'admin' ? 'Администратор' : 'Пользователь'; ?>
                </span>
            </p>
            <p><strong>Дата регистрации:</strong> <?php echo date('d.m.Y H:i', strtotime($user['created_at'])); ?></p>
        </div>
    </div>
<?php else: ?>
    <div class="alert alert-danger">Ошибка загрузки профиля</div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>

