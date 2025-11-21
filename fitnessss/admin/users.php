<?php
require_once '../config/config.php';
requireAdmin();
$pageTitle = 'Управление пользователями';
include '../includes/header.php';

$message = '';
$error = '';

// Обработка удаления
if (isset($_GET['delete']) && $_GET['delete'] > 0) {
    $delete_id = (int)$_GET['delete'];
    if ($delete_id != $_SESSION['user_id']) {
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$delete_id]);
            $message = 'Пользователь успешно удален';
        } catch (PDOException $e) {
            $error = 'Ошибка при удалении пользователя';
            error_log("Delete user error: " . $e->getMessage());
        }
    } else {
        $error = 'Нельзя удалить самого себя';
    }
}

try {
    $pdo = getDBConnection();
    $stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Users list error: " . $e->getMessage());
    $users = [];
}
?>

<h1>Управление пользователями</h1>

<?php if ($message): ?>
    <div class="alert alert-success"><?php echo $message; ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<div class="mb-3">
    <a href="user_edit.php" class="btn btn-success">+ Добавить пользователя</a>
</div>

<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Логин</th>
                <th>Email</th>
                <th>Полное имя</th>
                <th>Телефон</th>
                <th>Роль</th>
                <th>Дата регистрации</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo $user['id']; ?></td>
                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                    <td><?php echo htmlspecialchars($user['phone'] ?? '-'); ?></td>
                    <td>
                        <span class="badge <?php echo $user['role'] === 'admin' ? 'bg-danger' : 'bg-primary'; ?>">
                            <?php echo $user['role'] === 'admin' ? 'Админ' : 'Пользователь'; ?>
                        </span>
                    </td>
                    <td><?php echo date('d.m.Y H:i', strtotime($user['created_at'])); ?></td>
                    <td>
                        <a href="user_edit.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-primary">Редактировать</a>
                        <?php if ($user['id'] != $_SESSION['user_id']): ?>
                            <a href="?delete=<?php echo $user['id']; ?>" class="btn btn-sm btn-danger" 
                               onclick="return confirm('Вы уверены, что хотите удалить этого пользователя?')">Удалить</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>

