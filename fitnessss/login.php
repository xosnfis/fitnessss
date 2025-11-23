<?php
require_once 'config/config.php';

if (isLoggedIn()) {
    redirect('index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Введите логин и пароль';
    } else {
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("SELECT id, username, password, role, full_name FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['full_name'] = $user['full_name'];
                
                if ($user['role'] === 'admin') {
                    redirect('admin/index.php');
                } else {
                    redirect('index.php');
                }
            } else {
                $error = 'Неверный логин или пароль';
            }
        } catch (PDOException $e) {
            $error = 'Ошибка при входе. Попробуйте позже.';
            error_log("Login error: " . $e->getMessage());
        }
    }
}

$pageTitle = 'Вход';
include 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card login-card fade-in-on-scroll">
            <div class="card-header">
                <h2 class="mb-0">
                    <i class="fas fa-sign-in-alt me-2"></i>Вход в систему
                </h2>
            </div>
            <div class="card-body p-4">
                <?php if ($error): ?>
                    <div class="alert alert-danger fade-in-on-scroll">
                        <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                    </div>
                <?php endif; ?>
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="username" class="form-label">
                            <i class="fas fa-user me-2"></i>Логин или Email
                        </label>
                        <input type="text" class="form-control" id="username" name="username" required value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" placeholder="Введите логин или email">
                    </div>
                    <div class="mb-4">
                        <label for="password" class="form-label">
                            <i class="fas fa-lock me-2"></i>Пароль
                        </label>
                        <input type="password" class="form-control" id="password" name="password" required placeholder="Введите пароль">
                    </div>
                    <button type="submit" class="btn btn-primary w-100 mb-3 py-2">
                        <i class="fas fa-sign-in-alt me-2"></i>Войти
                    </button>
                    <div class="text-center">
                        <a href="register.php" class="text-decoration-none">
                            <i class="fas fa-user-plus me-1"></i>Нет аккаунта? Зарегистрироваться
                        </a>
                    </div>
                </form>
                <hr class="my-4">
                <div class="bg-light p-3 rounded">
                    <p class="text-muted small mb-2"><strong>Тестовые учетные данные:</strong></p>
                    <p class="text-muted small mb-1">
                        <i class="fas fa-user-shield me-1"></i>Админ: <code>admin</code> / <code>password</code>
                    </p>
                    <p class="text-muted small mb-0">
                        <i class="fas fa-user me-1"></i>Пользователь: <code>user1</code> / <code>password</code>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

