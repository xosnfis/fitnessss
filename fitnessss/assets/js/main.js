// Основной JavaScript файл

// Инициализация корзины
document.addEventListener('DOMContentLoaded', function() {
    // Обновление счетчика корзины
    updateCartCount();
    
    // Обработка форм с AJAX (если необходимо)
    const forms = document.querySelectorAll('form[data-ajax]');
    forms.forEach(form => {
        form.addEventListener('submit', handleAjaxForm);
    });
});

function updateCartCount() {
    const cart = JSON.parse(localStorage.getItem('cart') || '[]');
    const count = cart.reduce((sum, item) => sum + (item.quantity || 1), 0);
    const cartBadge = document.getElementById('cart-count');
    if (cartBadge) {
        cartBadge.textContent = count;
        cartBadge.style.display = count > 0 ? 'inline' : 'none';
    }
}

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
    
    // Показываем уведомление
    showNotification('Товар добавлен в корзину!', 'success');
}

function removeFromCart(type, id) {
    let cart = JSON.parse(localStorage.getItem('cart') || '[]');
    cart = cart.filter(item => !(item.type === type && item.id === id));
    localStorage.setItem('cart', JSON.stringify(cart));
    updateCartCount();
    location.reload(); // Перезагружаем страницу для обновления отображения
}

function clearCart() {
    localStorage.removeItem('cart');
    updateCartCount();
}

function showNotification(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    const container = document.querySelector('.container');
    if (container) {
        container.insertBefore(alertDiv, container.firstChild);
        
        // Автоматически скрыть через 3 секунды
        setTimeout(() => {
            alertDiv.remove();
        }, 3000);
    }
}

function handleAjaxForm(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    
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
            }
        } else {
            showNotification(data.message || 'Произошла ошибка!', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Произошла ошибка при отправке формы!', 'danger');
    });
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
        } else {
            field.classList.remove('is-invalid');
        }
    });
    
    return isValid;
}

