<?php
require_once '../config/config.php';
requireAdmin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$is_edit = $id > 0;

$error = '';
$message = '';

$pdo = getDBConnection();

// Загрузка данных пользователя для редактирования
$user = null;
if ($is_edit) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch();
    if (!$user) {
        $error = 'Пользователь не найден';
    }
}

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $full_name = sanitize($_POST['full_name'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $role = sanitize($_POST['role'] ?? 'user');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($email) || empty($full_name)) {
        $error = 'Все обязательные поля должны быть заполнены';
    } else {
        try {
            // Проверка уникальности
            $sql_check = "SELECT id FROM users WHERE (username = ? OR email = ?)";
            $params_check = [$username, $email];
            if ($is_edit) {
                $sql_check .= " AND id != ?";
                $params_check[] = $id;
            }
            $stmt = $pdo->prepare($sql_check);
            $stmt->execute($params_check);
            if ($stmt->fetch()) {
                $error = 'Пользователь с таким логином или email уже существует';
            } else {
                if ($is_edit) {
                    // Обновление
                    if (!empty($password)) {
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, full_name = ?, phone = ?, role = ?, password = ? WHERE id = ?");
                        $stmt->execute([$username, $email, $full_name, $phone, $role, $hashed_password, $id]);
                    } else {
                        $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, full_name = ?, phone = ?, role = ? WHERE id = ?");
                        $stmt->execute([$username, $email, $full_name, $phone, $role, $id]);
                    }
                    
                    // Если обновляем данные текущего пользователя, обновляем сессию
                    if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $id) {
                        $_SESSION['username'] = $username;
                        $_SESSION['role'] = $role;
                        $_SESSION['full_name'] = $full_name;
                        if (isset($_SESSION['email'])) {
                            $_SESSION['email'] = $email;
                        }
                    }
                    
                    $message = 'Пользователь успешно обновлен';
                } else {
                    // Создание
                    if (empty($password)) {
                        $error = 'Необходимо указать пароль для нового пользователя';
                    } else {
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, full_name, phone, role) VALUES (?, ?, ?, ?, ?, ?)");
                        $stmt->execute([$username, $email, $hashed_password, $full_name, $phone, $role]);
                        $message = 'Пользователь успешно создан';
                        $is_edit = true;
                        $id = $pdo->lastInsertId();
                    }
                }
            }
        } catch (PDOException $e) {
            $error = 'Ошибка при сохранении пользователя';
            error_log("User edit error: " . $e->getMessage());
        }
    }
    
    if ($message && !$error) {
        header('Refresh: 1; url=users.php');
    }
}

$pageTitle = $is_edit ? 'Редактирование пользователя' : 'Добавление пользователя';
include 'includes/header.php';
?>

<h1><?php echo $is_edit ? 'Редактирование пользователя' : 'Добавление пользователя'; ?></h1>

<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>
<?php if ($message): ?>
    <div class="alert alert-success"><?php echo $message; ?></div>
<?php endif; ?>

<?php if (!$error || !$message): ?>
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="username" class="form-label">Логин *</label>
                            <input type="text" class="form-control" id="username" name="username" required 
                                   value="<?php echo htmlspecialchars($user['username'] ?? $_POST['username'] ?? ''); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email *</label>
                            <input type="email" class="form-control" id="email" name="email" required 
                                   value="<?php echo htmlspecialchars($user['email'] ?? $_POST['email'] ?? ''); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="full_name" class="form-label">Полное имя *</label>
                            <input type="text" class="form-control" id="full_name" name="full_name" required 
                                   value="<?php echo htmlspecialchars($user['full_name'] ?? $_POST['full_name'] ?? ''); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">Телефон</label>
                            <input type="tel" class="form-control" id="phone" name="phone" 
                                   value="<?php echo htmlspecialchars($user['phone'] ?? $_POST['phone'] ?? ''); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="role" class="form-label">Роль *</label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="user" <?php echo ($user['role'] ?? 'user') === 'user' ? 'selected' : ''; ?>>Пользователь</option>
                                <option value="admin" <?php echo ($user['role'] ?? '') === 'admin' ? 'selected' : ''; ?>>Администратор</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Пароль <?php echo $is_edit ? '(оставьте пустым, чтобы не менять)' : '*'; ?></label>
                            <input type="password" class="form-control" id="password" name="password" <?php echo $is_edit ? '' : 'required'; ?>>
                        </div>
                        <button type="submit" class="btn btn-primary">Сохранить</button>
                        <a href="users.php" class="btn btn-secondary">Отмена</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>

