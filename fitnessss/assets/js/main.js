// Основной JavaScript файл с плавными анимациями

// Fallback для браузеров без requestIdleCallback
if (!window.requestIdleCallback) {
    window.requestIdleCallback = function(cb, options) {
        var start = Date.now();
        return setTimeout(function() {
            cb({
                didTimeout: false,
                timeRemaining: function() {
                    return Math.max(0, 50 - (Date.now() - start));
                }
            });
        }, options && options.timeout ? options.timeout : 1);
    };
}

// Инициализация при загрузке страницы - оптимизирована
document.addEventListener('DOMContentLoaded', function() {
    // Обновление счетчика корзины (быстрое, синхронное)
    updateCartCount();
    
    // Критичные инициализации - синхронно
    const forms = document.querySelectorAll('form[data-ajax]');
    forms.forEach(form => {
        form.addEventListener('submit', handleAjaxForm);
    });
    
    // Плавная прокрутка для якорных ссылок
    initSmoothScroll();
    
    // Неблокирующие операции - отложены для лучшей производительности
    requestIdleCallback(function() {
        // Анимация при прокрутке (отложено)
        initScrollAnimations();
        
        // Эффект для навигации при прокрутке (отложено)
        initNavbarScroll();
        
        // Анимация для карточек отключена - удаляем все 3D эффекты
        removeCard3DEffects();
    }, { timeout: 100 });
});

// Обновление счетчика корзины
function updateCartCount() {
    const cart = JSON.parse(localStorage.getItem('cart') || '[]');
    const count = cart.reduce((sum, item) => sum + (item.quantity || 1), 0);
    const cartBadge = document.getElementById('cart-count');
    if (cartBadge) {
        cartBadge.textContent = count;
        cartBadge.style.display = count > 0 ? 'inline-block' : 'none';
        
        // Анимация обновления
        cartBadge.style.animation = 'none';
        setTimeout(() => {
            cartBadge.style.animation = 'pulse 0.5s ease';
        }, 10);
    }
    
    // Обновление в навигации
    const navCartLinks = document.querySelectorAll('[data-cart-count]');
    navCartLinks.forEach(link => {
        const badge = link.querySelector('.badge');
        if (badge) {
            badge.textContent = count;
            badge.style.display = count > 0 ? 'inline-block' : 'none';
        }
    });
}

// Добавление товара в корзину с анимацией
function addToCart(item) {
    let cart = JSON.parse(localStorage.getItem('cart') || '[]');
    
    // Проверяем, есть ли уже такой товар в корзине
    const existingIndex = cart.findIndex(cartItem => 
        cartItem.type === item.type && cartItem.id === item.id
    );
    
    if (existingIndex !== -1) {
        cart[existingIndex].quantity = (cart[existingIndex].quantity || 1) + (item.quantity || 1);
    } else {
        cart.push(item);
    }
    
    localStorage.setItem('cart', JSON.stringify(cart));
    updateCartCount();
    
    // Показываем красивое уведомление
    showNotification('Товар добавлен в корзину!', 'success');
    
    // Анимация кнопки
    const button = event?.target;
    if (button) {
        button.classList.add('btn-animated');
        setTimeout(() => {
            button.classList.remove('btn-animated');
        }, 600);
    }
}

// Удаление из корзины
function removeFromCart(type, id) {
    let cart = JSON.parse(localStorage.getItem('cart') || '[]');
    cart = cart.filter(item => !(item.type === type && item.id === id));
    localStorage.setItem('cart', JSON.stringify(cart));
    updateCartCount();
    
    showNotification('Товар удален из корзины', 'info');
    
    // Плавное обновление страницы
    setTimeout(() => {
        location.reload();
    }, 300);
}

// Очистка корзины
function clearCart() {
    localStorage.removeItem('cart');
    updateCartCount();
    showNotification('Корзина очищена', 'info');
}

// Красивое уведомление
function showNotification(message, type = 'info') {
    // Удаляем предыдущие уведомления
    const existingNotifications = document.querySelectorAll('.notification');
    existingNotifications.forEach(notif => notif.remove());
    
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    
    const iconMap = {
        success: '✓',
        error: '✕',
        info: 'ℹ',
        warning: '⚠'
    };
    
    const colorMap = {
        success: 'linear-gradient(135deg, #06A77D, #07C8A0)',
        error: 'linear-gradient(135deg, #DC3545, #C82333)',
        info: 'linear-gradient(135deg, #1A659E, #4A90E2)',
        warning: 'linear-gradient(135deg, #FFC107, #FFB300)'
    };
    
    notification.style.background = colorMap[type] || colorMap.info;
    notification.innerHTML = `
        <div style="display: flex; align-items: center; gap: 10px;">
            <span style="font-size: 1.5rem;">${iconMap[type] || iconMap.info}</span>
            <span>${message}</span>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Анимация появления
    setTimeout(() => {
        notification.style.animation = 'slideInRight 0.5s ease';
    }, 10);
    
    // Автоматическое скрытие через 3 секунды
    setTimeout(() => {
        notification.style.animation = 'slideOutRight 0.5s ease';
        setTimeout(() => {
            notification.remove();
        }, 500);
    }, 3000);
}

// Обработка AJAX форм
function handleAjaxForm(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    const submitButton = form.querySelector('button[type="submit"]');
    
    // Показываем состояние загрузки
    if (submitButton) {
        const originalText = submitButton.innerHTML;
        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Загрузка...';
        submitButton.classList.add('loading');
        
        fetch(form.action, {
            method: form.method || 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message || 'Операция выполнена успешно!', 'success');
                if (data.redirect) {
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 1000);
                } else {
                    form.reset();
                }
            } else {
                showNotification(data.message || 'Произошла ошибка!', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Произошла ошибка при отправке формы!', 'error');
        })
        .finally(() => {
            if (submitButton) {
                submitButton.disabled = false;
                submitButton.innerHTML = originalText;
                submitButton.classList.remove('loading');
            }
        });
    }
}

// Анимации при прокрутке - оптимизирована
function initScrollAnimations() {
    const elements = document.querySelectorAll('.fade-in-on-scroll');
    
    // Используем более легкий IntersectionObserver с меньшим порогом
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                requestAnimationFrame(() => {
                    entry.target.classList.add('visible');
                    observer.unobserve(entry.target);
                });
            }
        });
    }, {
        threshold: 0.05,
        rootMargin: '50px'
    });
    
    elements.forEach(element => {
        // Добавляем класс только для элементов, которые должны анимироваться
        if (!element.classList.contains('visible')) {
            element.classList.add('animate-on-load');
            observer.observe(element);
        }
    });
}

// Эффект для навигации при прокрутке - оптимизирован
function initNavbarScroll() {
    const navbar = document.querySelector('.navbar');
    if (!navbar) return;
    
    let ticking = false;
    let isScrolled = false;
    
    window.addEventListener('scroll', () => {
        if (!ticking) {
            window.requestAnimationFrame(() => {
                const currentScroll = window.pageYOffset;
                const shouldBeScrolled = currentScroll > 50;
                
                if (shouldBeScrolled !== isScrolled) {
                    if (shouldBeScrolled) {
                        navbar.classList.add('scrolled');
                    } else {
                        navbar.classList.remove('scrolled');
                    }
                    isScrolled = shouldBeScrolled;
                }
                
                ticking = false;
            });
            ticking = true;
        }
    }, { passive: true });
}

// Плавная прокрутка
function initSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href === '#') return;
            
            e.preventDefault();
            const target = document.querySelector(href);
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}

// Удаление всех 3D эффектов с карточек
function removeCard3DEffects() {
    // Добавляем CSS правило, которое блокирует все transform на карточках
    const style = document.createElement('style');
    style.textContent = `
        .card {
            transform: none !important;
            perspective: none !important;
        }
        .card * {
            transform: none !important;
        }
    `;
    document.head.appendChild(style);
    
    // Удаляем все inline transform стили с карточек
    const removeTransforms = () => {
        const cards = document.querySelectorAll('.card');
        cards.forEach(card => {
            if (card.style.transform && card.style.transform.includes('perspective')) {
                card.style.transform = '';
            }
            if (card.style.perspective) {
                card.style.perspective = '';
            }
        });
    };
    
    // Удаляем сразу
    removeTransforms();
    
    // Удаляем периодически на случай, если что-то пытается их применить
    setInterval(removeTransforms, 100);
    
    // Удаляем при любом движении мыши
    document.addEventListener('mousemove', function(e) {
        const card = e.target.closest('.card');
        if (card) {
            if (card.style.transform && card.style.transform.includes('perspective')) {
                card.style.transform = '';
            }
        }
    }, { passive: true });
}

// Старая функция - больше не используется
function initCardAnimations() {
    // Функция отключена
}

// Валидация форм
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return false;
    
    const requiredFields = form.querySelectorAll('[required]');
    let isValid = true;
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            isValid = false;
            field.classList.add('is-invalid');
            
            // Анимация ошибки
            field.style.animation = 'shake 0.5s ease';
            setTimeout(() => {
                field.style.animation = '';
            }, 500);
        } else {
            field.classList.remove('is-invalid');
            field.classList.add('is-valid');
        }
    });
    
    return isValid;
}

// Анимация встряхивания для ошибок
const shakeKeyframes = `
@keyframes shake {
    0%, 100% { transform: translateX(0); }
    10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
    20%, 40%, 60%, 80% { transform: translateX(5px); }
}

@keyframes slideOutRight {
    from {
        opacity: 1;
        transform: translateX(0);
    }
    to {
        opacity: 0;
        transform: translateX(100px);
    }
}
`;

// Добавляем CSS анимации
const style = document.createElement('style');
style.textContent = shakeKeyframes;
document.head.appendChild(style);

// Улучшение для кнопок
document.addEventListener('click', function(e) {
    if (e.target.matches('.btn, button')) {
        const button = e.target;
        const ripple = document.createElement('span');
        const rect = button.getBoundingClientRect();
        const size = Math.max(rect.width, rect.height);
        const x = e.clientX - rect.left - size / 2;
        const y = e.clientY - rect.top - size / 2;
        
        ripple.style.width = ripple.style.height = size + 'px';
        ripple.style.left = x + 'px';
        ripple.style.top = y + 'px';
        ripple.classList.add('ripple');
        
        button.appendChild(ripple);
        
        setTimeout(() => {
            ripple.remove();
        }, 600);
    }
});

// Добавляем стили для ripple эффекта
const rippleStyle = document.createElement('style');
rippleStyle.textContent = `
.ripple {
    position: absolute;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.6);
    transform: scale(0);
    animation: ripple 0.6s ease-out;
    pointer-events: none;
}

@keyframes ripple {
    to {
        transform: scale(2);
        opacity: 0;
    }
}

.btn {
    position: relative;
    overflow: hidden;
}
`;
document.head.appendChild(rippleStyle);

// Обработка изображений с ленивой загрузкой
if ('IntersectionObserver' in window) {
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                if (img.dataset.src) {
                    img.src = img.dataset.src;
                    img.classList.add('loaded');
                    observer.unobserve(img);
                }
            }
        });
    });
    
    document.querySelectorAll('img[data-src]').forEach(img => {
        imageObserver.observe(img);
    });
}

// Оптимизация производительности - удалено дублирование
