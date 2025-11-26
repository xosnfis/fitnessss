<?php
require_once '../config/config.php';
requireAdmin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$is_edit = $id > 0;

$error = '';
$message = '';

$pdo = getDBConnection();

// Загрузка данных тренера
$trainer = null;
$trainer_photos = [];
if ($is_edit) {
    $stmt = $pdo->prepare("SELECT * FROM trainers WHERE id = ?");
    $stmt->execute([$id]);
    $trainer = $stmt->fetch();
    if (!$trainer) {
        $error = 'Тренер не найден';
    } else {
        // Загружаем фотографии тренера
        $photos_stmt = $pdo->prepare("SELECT * FROM trainer_photos WHERE trainer_id = ? ORDER BY display_order ASC, id ASC");
        $photos_stmt->execute([$id]);
        $trainer_photos = $photos_stmt->fetchAll();
        
        // Если нет фотографий в новой таблице, используем старое поле photo для обратной совместимости
        if (empty($trainer_photos) && !empty($trainer['photo'])) {
            $trainer_photos = [['photo_path' => $trainer['photo'], 'id' => 0]];
        }
    }
}

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitize($_POST['full_name'] ?? '');
    $specialization = sanitize($_POST['specialization'] ?? '');
    $experience = (int)($_POST['experience'] ?? 0);
    $phone = sanitize($_POST['phone'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $bio = sanitize($_POST['bio'] ?? '');
    $schedule = sanitize($_POST['schedule'] ?? '');
    
    if (empty($full_name) || empty($specialization) || $experience < 0) {
        $error = 'Заполните все обязательные поля';
    } else {
        try {
            $pdo->beginTransaction();
            
            if ($is_edit) {
                $stmt = $pdo->prepare("UPDATE trainers SET full_name = ?, specialization = ?, experience = ?, phone = ?, email = ?, bio = ?, schedule = ? WHERE id = ?");
                $stmt->execute([$full_name, $specialization, $experience, $phone, $email, $bio, $schedule, $id]);
                $message = 'Тренер успешно обновлен';
            } else {
                $stmt = $pdo->prepare("INSERT INTO trainers (full_name, specialization, experience, phone, email, bio, schedule) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$full_name, $specialization, $experience, $phone, $email, $bio, $schedule]);
                $id = $pdo->lastInsertId();
                $is_edit = true;
                $message = 'Тренер успешно создан';
            }
            
            // Обработка загрузки нескольких фотографий
            if (isset($_FILES['photos']) && !empty($_FILES['photos']['name'][0])) {
                $upload_dir = '../uploads/trainers/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                $max_size = 5 * 1024 * 1024; // 5MB
                
                foreach ($_FILES['photos']['name'] as $key => $filename) {
                    if ($_FILES['photos']['error'][$key] === UPLOAD_ERR_OK) {
                        $file = [
                            'name' => $_FILES['photos']['name'][$key],
                            'type' => $_FILES['photos']['type'][$key],
                            'tmp_name' => $_FILES['photos']['tmp_name'][$key],
                            'size' => $_FILES['photos']['size'][$key]
                        ];
                        
                        if (in_array($file['type'], $allowed_types) && $file['size'] <= $max_size) {
                            $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                            $file_name = uniqid('trainer_', true) . '.' . $file_extension;
                            $file_path = $upload_dir . $file_name;
                            
                            if (move_uploaded_file($file['tmp_name'], $file_path)) {
                                $photo_path = 'uploads/trainers/' . $file_name;
                                // Получаем максимальный display_order
                                $order_stmt = $pdo->prepare("SELECT COALESCE(MAX(display_order), 0) + 1 as next_order FROM trainer_photos WHERE trainer_id = ?");
                                $order_stmt->execute([$id]);
                                $next_order = $order_stmt->fetch()['next_order'];
                                
                                $photo_stmt = $pdo->prepare("INSERT INTO trainer_photos (trainer_id, photo_path, display_order) VALUES (?, ?, ?)");
                                $photo_stmt->execute([$id, $photo_path, $next_order]);
                            }
                        }
                    }
                }
            }
            
            // Обработка удаления фотографий
            if (isset($_POST['delete_photos']) && is_array($_POST['delete_photos'])) {
                foreach ($_POST['delete_photos'] as $photo_id) {
                    $photo_id = (int)$photo_id;
                    if ($photo_id > 0) {
                        // Получаем путь к файлу
                        $photo_stmt = $pdo->prepare("SELECT photo_path FROM trainer_photos WHERE id = ? AND trainer_id = ?");
                        $photo_stmt->execute([$photo_id, $id]);
                        $photo_data = $photo_stmt->fetch();
                        
                        if ($photo_data) {
                            // Удаляем файл
                            $file_path = '../' . $photo_data['photo_path'];
                            if (file_exists($file_path)) {
                                unlink($file_path);
                            }
                            // Удаляем запись из БД
                            $delete_stmt = $pdo->prepare("DELETE FROM trainer_photos WHERE id = ? AND trainer_id = ?");
                            $delete_stmt->execute([$photo_id, $id]);
                        }
                    }
                }
            }
            
            // Обновление порядка фотографий
            if (isset($_POST['photo_order']) && is_array($_POST['photo_order'])) {
                foreach ($_POST['photo_order'] as $photo_id => $order) {
                    $photo_id = (int)$photo_id;
                    $order = (int)$order;
                    if ($photo_id > 0) {
                        $order_stmt = $pdo->prepare("UPDATE trainer_photos SET display_order = ? WHERE id = ? AND trainer_id = ?");
                        $order_stmt->execute([$order, $photo_id, $id]);
                    }
                }
            }
            
            $pdo->commit();
            
            // Перезагружаем данные тренера
            if ($is_edit) {
                $photos_stmt = $pdo->prepare("SELECT * FROM trainer_photos WHERE trainer_id = ? ORDER BY display_order ASC, id ASC");
                $photos_stmt->execute([$id]);
                $trainer_photos = $photos_stmt->fetchAll();
            }
            
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = 'Ошибка при сохранении тренера';
            error_log("Trainer edit error: " . $e->getMessage());
        }
    }
    
    if ($message && !$error && isset($_POST['save_and_stay'])) {
        // Остаемся на странице редактирования
    } elseif ($message && !$error) {
        header('Refresh: 1; url=trainers.php');
    }
}

$pageTitle = $is_edit ? 'Редактирование тренера' : 'Добавление тренера';
include 'includes/header.php';
?>

<h1><?php echo $is_edit ? 'Редактирование тренера' : 'Добавление тренера'; ?></h1>

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
                    <form method="POST" action="" enctype="multipart/form-data">
                        <!-- Поле для фотографий -->
                        <div class="mb-4">
                            <label for="photos" class="form-label">Фотографии тренера</label>
                            
                            <?php if ($is_edit && !empty($trainer_photos)): ?>
                                <div class="mb-3">
                                    <h6>Текущие фотографии:</h6>
                                    <div class="row g-2">
                                        <?php foreach ($trainer_photos as $index => $photo): ?>
                                            <div class="col-md-3 col-sm-4">
                                                <div class="position-relative">
                                                    <img src="../<?php echo htmlspecialchars($photo['photo_path']); ?>" 
                                                         alt="Фото <?php echo $index + 1; ?>"
                                                         class="img-thumbnail w-100" 
                                                         style="height: 150px; object-fit: cover;">
                                                    <div class="form-check position-absolute top-0 end-0 m-1">
                                                        <input class="form-check-input" 
                                                               type="checkbox" 
                                                               name="delete_photos[]" 
                                                               value="<?php echo $photo['id']; ?>" 
                                                               id="deletePhoto<?php echo $photo['id']; ?>">
                                                        <label class="form-check-label" for="deletePhoto<?php echo $photo['id']; ?>" title="Удалить">
                                                            <i class="fas fa-times text-danger"></i>
                                                        </label>
                                                    </div>
                                                    <input type="number" 
                                                           name="photo_order[<?php echo $photo['id']; ?>]" 
                                                           value="<?php echo $photo['display_order']; ?>" 
                                                           class="form-control form-control-sm mt-1" 
                                                           placeholder="Порядок" 
                                                           min="0">
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <small class="text-muted d-block mt-2">Отметьте фотографии для удаления. Укажите порядок отображения (чем меньше число, тем выше в слайдере).</small>
                                </div>
                            <?php endif; ?>
                            
                            <input type="file" class="form-control" id="photos" name="photos[]" accept="image/*" multiple>
                            <small class="text-muted">Разрешены форматы: JPG, PNG, GIF. Максимальный размер: 5MB на файл. Можно выбрать несколько файлов.</small>
                            <div id="photoPreview" class="mt-3"></div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="full_name" class="form-label">ФИО *</label>
                            <input type="text" class="form-control" id="full_name" name="full_name" required 
                                   value="<?php echo htmlspecialchars($trainer['full_name'] ?? $_POST['full_name'] ?? ''); ?>">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="specialization" class="form-label">Специализация *</label>
                                <input type="text" class="form-control" id="specialization" name="specialization" required 
                                       value="<?php echo htmlspecialchars($trainer['specialization'] ?? $_POST['specialization'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="experience" class="form-label">Опыт работы (лет) *</label>
                                <input type="number" class="form-control" id="experience" name="experience" min="0" required 
                                       value="<?php echo $trainer['experience'] ?? $_POST['experience'] ?? ''; ?>">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Телефон</label>
                                <input type="tel" class="form-control" id="phone" name="phone" 
                                       value="<?php echo htmlspecialchars($trainer['phone'] ?? $_POST['phone'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($trainer['email'] ?? $_POST['email'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="schedule" class="form-label">Расписание работы</label>
                            <input type="text" class="form-control" id="schedule" name="schedule" 
                                   value="<?php echo htmlspecialchars($trainer['schedule'] ?? $_POST['schedule'] ?? ''); ?>"
                                   placeholder="Например: Пн-Пт: 10:00-18:00">
                        </div>
                        <div class="mb-3">
                            <label for="bio" class="form-label">Биография</label>
                            <textarea class="form-control" id="bio" name="bio" rows="3"><?php echo htmlspecialchars($trainer['bio'] ?? $_POST['bio'] ?? ''); ?></textarea>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" name="save_and_stay" value="1" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Сохранить и остаться
                            </button>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-check me-1"></i>Сохранить
                            </button>
                            <a href="trainers.php" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>Отмена
                            </a>
                        </div>
                    </form>
                    
                    <script>
                    // Предпросмотр загружаемых фотографий
                    document.getElementById('photos').addEventListener('change', function(e) {
                        const files = e.target.files;
                        const previewDiv = document.getElementById('photoPreview');
                        previewDiv.innerHTML = '';
                        
                        if (files.length > 0) {
                            const label = document.createElement('label');
                            label.className = 'form-label';
                            label.textContent = 'Предпросмотр новых фотографий:';
                            previewDiv.appendChild(label);
                            
                            const row = document.createElement('div');
                            row.className = 'row g-2 mt-2';
                            
                            Array.from(files).forEach((file, index) => {
                                if (file.type.startsWith('image/')) {
                                    const reader = new FileReader();
                                    reader.onload = function(e) {
                                        const col = document.createElement('div');
                                        col.className = 'col-md-3 col-sm-4';
                                        col.innerHTML = `
                                            <img src="${e.target.result}" 
                                                 class="img-thumbnail w-100" 
                                                 style="height: 150px; object-fit: cover;"
                                                 alt="Предпросмотр ${index + 1}">
                                        `;
                                        row.appendChild(col);
                                    };
                                    reader.readAsDataURL(file);
                                }
                            });
                            
                            previewDiv.appendChild(row);
                        }
                    });
                    </script>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>

