<?php
require_once 'config/config.php';

if (isLoggedIn()) {
    redirect('index.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $full_name = sanitize($_POST['full_name'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    
    // Валидация
    if (empty($username) || empty($email) || empty($password) || empty($full_name)) {
        $error = 'Все обязательные поля должны быть заполнены';
    } elseif ($password !== $password_confirm) {
        $error = 'Пароли не совпадают';
    } elseif (strlen($password) < 6) {
        $error = 'Пароль должен содержать минимум 6 символов';
    } else {
        try {
            $pdo = getDBConnection();
            
            // Проверка существования пользователя
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            if ($stmt->fetch()) {
                $error = 'Пользователь с таким логином или email уже существует';
            } else {
                // Создание пользователя
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password, full_name, phone) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$username, $email, $hashed_password, $full_name, $phone]);
                
                $success = 'Регистрация успешна! Теперь вы можете войти.';
                header('Refresh: 2; url=login.php');
            }
        } catch (PDOException $e) {
            $error = 'Ошибка при регистрации. Попробуйте позже.';
            error_log("Registration error: " . $e->getMessage());
        }
    }
}

$pageTitle = 'Регистрация';
include 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h2>Регистрация</h2>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php else: ?>
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="username" class="form-label">Логин *</label>
                            <input type="text" class="form-control" id="username" name="username" required value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email *</label>
                            <input type="email" class="form-control" id="email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="full_name" class="form-label">Полное имя *</label>
                            <input type="text" class="form-control" id="full_name" name="full_name" required value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">Телефон</label>
                            <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Пароль *</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label for="password_confirm" class="form-label">Подтвердите пароль *</label>
                            <input type="password" class="form-control" id="password_confirm" name="password_confirm" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Зарегистрироваться</button>
                        <a href="login.php" class="btn btn-link">Уже есть аккаунт? Войти</a>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

