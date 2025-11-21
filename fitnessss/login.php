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
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h2>Вход в систему</h2>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="username" class="form-label">Логин или Email</label>
                        <input type="text" class="form-control" id="username" name="username" required value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Пароль</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Войти</button>
                    <a href="register.php" class="btn btn-link">Нет аккаунта? Зарегистрироваться</a>
                </form>
                <hr>
                <p class="text-muted small">Тестовые учетные данные:<br>
                Админ: admin / password<br>
                Пользователь: user1 / password</p>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

