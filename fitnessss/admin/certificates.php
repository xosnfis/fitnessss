<?php
require_once '../config/config.php';
requireAdmin();
$pageTitle = 'Сертификаты';
include 'includes/header.php';

$message = '';
$error = '';

// Получаем список сертификатов
try {
    $pdo = getDBConnection();
    $stmt = $pdo->query("SELECT * FROM certificates ORDER BY display_order ASC, created_at DESC");
    $certificates = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Certificates error: " . $e->getMessage());
    $certificates = [];
    $error = 'Ошибка загрузки сертификатов';
}
?>

<h1 class="admin-page-title fade-in-on-scroll">
    <i class="fas fa-certificate"></i>Управление сертификатами
</h1>

<?php if ($error): ?>
    <div class="alert alert-danger fade-in-on-scroll">
        <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
    </div>
<?php endif; ?>

<?php if ($message): ?>
    <div class="alert alert-success fade-in-on-scroll">
        <i class="fas fa-check-circle me-2"></i><?php echo $message; ?>
    </div>
<?php endif; ?>

<div class="row mb-4">
    <div class="col-12">
        <div class="card fade-in-on-scroll">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i>Список сертификатов
                </h5>
                <button type="button" class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#uploadModal">
                    <i class="fas fa-plus me-1"></i>Загрузить сертификат
                </button>
            </div>
            <div class="card-body">
                <?php if (empty($certificates)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>Сертификаты не загружены. Нажмите "Загрузить сертификат" для добавления.
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($certificates as $cert): ?>
                            <div class="col-md-4 col-lg-3 mb-4">
                                <div class="card h-100">
                                    <div class="card-img-top position-relative" style="height: 200px; overflow: hidden; background: #f8f9fa;">
                                        <img src="../<?php echo htmlspecialchars($cert['image_path']); ?>" 
                                             alt="<?php echo htmlspecialchars($cert['title']); ?>"
                                             class="w-100 h-100" 
                                             style="object-fit: cover; cursor: pointer;"
                                             onclick="openImageModal('<?php echo htmlspecialchars($cert['image_path']); ?>', '<?php echo htmlspecialchars($cert['title']); ?>')">
                                        <?php if (!$cert['is_active']): ?>
                                            <span class="badge bg-secondary position-absolute top-0 start-0 m-2">Неактивен</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="card-body">
                                        <h6 class="card-title"><?php echo htmlspecialchars($cert['title']); ?></h6>
                                        <?php if ($cert['description']): ?>
                                            <p class="card-text small text-muted"><?php echo htmlspecialchars(mb_substr($cert['description'], 0, 50)); ?>...</p>
                                        <?php endif; ?>
                                        <div class="d-flex gap-2 mt-2">
                                            <button class="btn btn-danger btn-sm flex-fill" 
                                                    onclick="deleteCertificate(<?php echo $cert['id']; ?>, '<?php echo htmlspecialchars($cert['title'], ENT_QUOTES); ?>')">
                                                <i class="fas fa-trash me-1"></i>Удалить
                                            </button>
                                            <button class="btn btn-secondary btn-sm" 
                                                    onclick="toggleActive(<?php echo $cert['id']; ?>, <?php echo $cert['is_active'] ? 0 : 1; ?>)">
                                                <i class="fas fa-<?php echo $cert['is_active'] ? 'eye-slash' : 'eye'; ?>"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно для загрузки -->
<div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="uploadModalLabel">
                    <i class="fas fa-upload me-2"></i>Загрузить сертификат
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="uploadForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="certificateTitle" class="form-label">Название сертификата *</label>
                        <input type="text" class="form-control" id="certificateTitle" name="title" required 
                               placeholder="Например: Сертификат по фитнесу">
                    </div>
                    <div class="mb-3">
                        <label for="certificateDescription" class="form-label">Описание</label>
                        <textarea class="form-control" id="certificateDescription" name="description" rows="3" 
                                  placeholder="Описание сертификата (необязательно)"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="certificateImage" class="form-label">Изображение сертификата *</label>
                        <input type="file" class="form-control" id="certificateImage" name="image" accept="image/*" required>
                        <small class="text-muted">Разрешены форматы: JPG, PNG, GIF. Максимальный размер: 5MB</small>
                    </div>
                    <div class="mb-3">
                        <label for="displayOrder" class="form-label">Порядок отображения</label>
                        <input type="number" class="form-control" id="displayOrder" name="display_order" value="0" min="0">
                        <small class="text-muted">Чем меньше число, тем выше в списке</small>
                    </div>
                    <div id="uploadMessage"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload me-1"></i>Загрузить
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Модальное окно для просмотра изображения -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imageModalTitle"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-0">
                <img id="imageModalImg" src="" alt="" class="img-fluid">
            </div>
        </div>
    </div>
</div>

<script>
// Обработка формы загрузки
document.getElementById('uploadForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const form = e.target;
    const formData = new FormData(form);
    const submitBtn = form.querySelector('button[type="submit"]');
    const messageDiv = document.getElementById('uploadMessage');
    const originalBtnText = submitBtn.innerHTML;
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Загрузка...';
    messageDiv.innerHTML = '';
    
    fetch('api/upload_certificate.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            messageDiv.innerHTML = '<div class="alert alert-success"><i class="fas fa-check-circle me-2"></i>' + data.message + '</div>';
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            messageDiv.innerHTML = '<div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i>' + (data.message || 'Ошибка при загрузке') + '</div>';
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        messageDiv.innerHTML = '<div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i>Произошла ошибка при загрузке</div>';
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalBtnText;
    });
});

// Удаление сертификата
function deleteCertificate(id, title) {
    if (!confirm('Вы уверены, что хотите удалить сертификат "' + title + '"?\n\nЭто действие нельзя отменить.')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('certificate_id', id);
    
    fetch('api/delete_certificate.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Сертификат успешно удален');
            window.location.reload();
        } else {
            alert('Ошибка: ' + (data.message || 'Не удалось удалить сертификат'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Произошла ошибка при удалении сертификата');
    });
}

// Переключение активности
function toggleActive(id, newStatus) {
    const formData = new FormData();
    formData.append('certificate_id', id);
    formData.append('is_active', newStatus);
    
    fetch('api/toggle_certificate.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            alert('Ошибка: ' + (data.message || 'Не удалось изменить статус'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Произошла ошибка');
    });
}

// Открытие модального окна с изображением
function openImageModal(imagePath, title) {
    document.getElementById('imageModalImg').src = '../' + imagePath;
    document.getElementById('imageModalTitle').textContent = title;
    const modal = new bootstrap.Modal(document.getElementById('imageModal'));
    modal.show();
}
</script>

<?php include 'includes/footer.php'; ?>

