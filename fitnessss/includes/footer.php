    </main>
    <footer class="text-white text-center py-5">
        <div class="container">
            <div class="row mb-4">
                <div class="col-md-4 mb-3 mb-md-0">
                    <h5 class="mb-3 footer-hover"><i class="fas fa-dumbbell me-2"></i>Фитнес-центр</h5>
                    <p class="mb-0 footer-text-hover">Ваш путь к здоровому образу жизни начинается здесь</p>
                    <p class="mb-0 mt-2">
                        <a href="faq.php" class="text-white text-decoration-none footer-link-hover">
                            <i class="fas fa-question-circle me-1"></i>Часто задаваемые вопросы
                        </a>
                    </p>
                </div>
                <div class="col-md-4 mb-3 mb-md-0">
                    <h5 class="mb-3 footer-hover">Контакты</h5>
                    <p class="mb-2 footer-text-hover">
                        <i class="fas fa-envelope me-2"></i>
                        <a href="#" class="text-white text-decoration-none footer-link-hover" data-bs-toggle="modal" data-bs-target="#contactModal"><?php echo ADMIN_EMAIL; ?></a>
                    </p>
                    <p class="mb-0 footer-text-hover">
                        <i class="fas fa-phone me-2"></i>
                        <a href="tel:+79991234567" class="text-white text-decoration-none footer-link-hover">+7 (999) 123-45-67</a>
                    </p>
                </div>
                <div class="col-md-4">
                    <h5 class="mb-3">Мы в соцсетях</h5>
                    <div class="d-flex justify-content-center gap-3">
                        <a href="#" class="text-white fs-4" aria-label="Facebook"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="text-white fs-4" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-white fs-4" aria-label="Telegram"><i class="fab fa-telegram"></i></a>
                        <a href="#" class="text-white fs-4" aria-label="VKontakte"><i class="fab fa-vk"></i></a>
                    </div>
                </div>
            </div>
            <hr class="my-4" style="border-color: rgba(255,255,255,0.1);">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> Фитнес-центр. Все права защищены.</p>
        </div>
    </footer>
    <!-- Модальное окно обратной связи (доступно на всех страницах) -->
    <div class="modal fade" id="contactModal" tabindex="-1" aria-labelledby="contactModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="contactModalLabel">
                        <i class="fas fa-envelope me-2 text-primary"></i>Написать нам
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                </div>
                <div class="modal-body">
                    <form id="contactForm" method="POST" action="">
                        <div class="mb-3">
                            <label for="contactName" class="form-label">
                                <i class="fas fa-user me-1"></i>Ваше имя *
                            </label>
                            <input type="text" class="form-control" id="contactName" name="name" required placeholder="Введите ваше имя">
                        </div>
                        <div class="mb-3">
                            <label for="contactEmail" class="form-label">
                                <i class="fas fa-envelope me-1"></i>Email *
                            </label>
                            <input type="email" class="form-control" id="contactEmail" name="email" required placeholder="your@email.com">
                        </div>
                        <div class="mb-3">
                            <label for="contactPhone" class="form-label">
                                <i class="fas fa-phone me-1"></i>Телефон
                            </label>
                            <input type="tel" class="form-control" id="contactPhone" name="phone" placeholder="+7 (999) 123-45-67">
                        </div>
                        <div class="mb-3">
                            <label for="contactSubject" class="form-label">
                                <i class="fas fa-tag me-1"></i>Тема *
                            </label>
                            <select class="form-select" id="contactSubject" name="subject" required>
                                <option value="">Выберите тему</option>
                                <option value="question">Вопрос об услугах</option>
                                <option value="subscription">Вопрос об абонементах</option>
                                <option value="training">Вопрос о тренировках</option>
                                <option value="technical">Техническая проблема</option>
                                <option value="other">Другое</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="contactMessage" class="form-label">
                                <i class="fas fa-comment me-1"></i>Сообщение *
                            </label>
                            <textarea class="form-control" id="contactMessage" name="message" rows="5" required placeholder="Опишите ваш вопрос или проблему..."></textarea>
                        </div>
                        <div id="contactFormMessage"></div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Отмена
                    </button>
                    <button type="submit" form="contactForm" class="btn btn-primary">
                        <i class="fas fa-paper-plane me-2"></i>Отправить
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Показываем страницу после загрузки
        document.body.classList.add('loaded');
        
        // Обработка формы обратной связи (глобально для всех страниц)
        const contactForm = document.getElementById('contactForm');
        if (contactForm) {
            contactForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const form = this;
                const formData = new FormData(form);
                const submitBtn = form.querySelector('button[type="submit"]');
                const messageDiv = document.getElementById('contactFormMessage');
                const originalBtnText = submitBtn.innerHTML;
                
                // Блокируем кнопку
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Отправка...';
                messageDiv.innerHTML = '';
                
                // Отправка формы через AJAX
                fetch('api/contact.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        messageDiv.innerHTML = '<div class="alert alert-success"><i class="fas fa-check-circle me-2"></i>' + data.message + '</div>';
                        form.reset();
                        
                        // Закрываем модальное окно через 2 секунды
                        setTimeout(() => {
                            const modal = bootstrap.Modal.getInstance(document.getElementById('contactModal'));
                            if (modal) {
                                modal.hide();
                            }
                            messageDiv.innerHTML = '';
                        }, 2000);
                    } else {
                        messageDiv.innerHTML = '<div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i>' + data.message + '</div>';
                    }
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                })
                .catch(error => {
                    console.error('Error:', error);
                    messageDiv.innerHTML = '<div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i>Произошла ошибка при отправке. Попробуйте позже.</div>';
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                });
            });
            
            // Очистка формы при закрытии модального окна
            document.getElementById('contactModal').addEventListener('hidden.bs.modal', function() {
                contactForm.reset();
                document.getElementById('contactFormMessage').innerHTML = '';
            });
        }
    </script>
    <script src="assets/js/main.js"></script>
</body>
</html>

