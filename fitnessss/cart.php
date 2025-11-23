<?php
require_once 'config/config.php';
requireLogin();
$pageTitle = 'Корзина';
include 'includes/header.php';

// Получение корзины из localStorage через JavaScript будет обрабатываться на клиенте
?>

<h1 class="mb-4 fade-in-on-scroll">
    <i class="fas fa-shopping-cart text-primary me-2"></i>Корзина
</h1>

<div id="cart-items" class="fade-in-on-scroll">
    <div class="alert alert-info">
        <i class="fas fa-spinner fa-spin me-2"></i>Загрузка корзины...
    </div>
</div>

<div id="cart-summary" class="card mt-4 fade-in-on-scroll" style="display: none;">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="mb-0">Итого:</h5>
            <h3 class="price mb-0" id="total-amount">0 ₽</h3>
        </div>
        <form method="POST" action="checkout.php" id="checkout-form">
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-success btn-lg py-3">
                    <i class="fas fa-check-circle me-2"></i>Оформить заказ
                </button>
                <button type="button" class="btn btn-secondary" onclick="clearCart(); location.reload();">
                    <i class="fas fa-trash me-2"></i>Очистить корзину
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadCart();
});

function loadCart() {
    const cart = JSON.parse(localStorage.getItem('cart') || '[]');
    const cartItemsDiv = document.getElementById('cart-items');
    const cartSummary = document.getElementById('cart-summary');
    const totalAmountSpan = document.getElementById('total-amount');
    
    if (cart.length === 0) {
        cartItemsDiv.innerHTML = `
            <div class="alert alert-warning fade-in-on-scroll">
                <i class="fas fa-shopping-cart me-2"></i>Ваша корзина пуста
                <p class="mb-0 mt-2">
                    <a href="services.php" class="alert-link">Посмотрите наши услуги</a> или 
                    <a href="subscriptions.php" class="alert-link">выберите абонемент</a>
                </p>
            </div>
        `;
        cartSummary.style.display = 'none';
        return;
    }
    
    let html = '';
    let total = 0;
    
    cart.forEach((item, index) => {
        const subtotal = (item.price || 0) * (item.quantity || 1);
        total += subtotal;
        
        html += `
            <div class="cart-item card mb-3 fade-in-on-scroll">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-md-5">
                            <h5 class="mb-2">
                                <i class="fas ${item.type === 'service' ? 'fa-dumbbell' : 'fa-id-card'} text-primary me-2"></i>
                                ${escapeHtml(item.name)}
                            </h5>
                            <span class="badge ${item.type === 'service' ? 'bg-primary' : 'bg-success'}">
                                ${item.type === 'service' ? 'Услуга' : 'Абонемент'}
                            </span>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label mb-2">Количество:</label>
                            <input type="number" class="form-control" value="${item.quantity || 1}" min="1" 
                                   onchange="updateQuantity(${index}, this.value)" style="max-width: 100px;">
                        </div>
                        <div class="col-md-2 text-center">
                            <div class="mb-2">
                                <strong class="fs-5 text-primary">${formatPrice(subtotal)} ₽</strong>
                            </div>
                            <small class="text-muted">${formatPrice(item.price)} ₽ за ед.</small>
                        </div>
                        <div class="col-md-2 text-end">
                            <button class="btn btn-danger btn-sm" onclick="removeFromCart('${item.type}', ${item.id})">
                                <i class="fas fa-trash me-1"></i>Удалить
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });
    
    cartItemsDiv.innerHTML = html;
    totalAmountSpan.textContent = formatPrice(total) + ' ₽';
    cartSummary.style.display = 'block';
    
    // Сохранение данных корзины в скрытое поле формы для отправки на сервер
    const form = document.getElementById('checkout-form');
    // Удаляем старое поле если есть
    const oldInput = form.querySelector('input[name="cart_data"]');
    if (oldInput) {
        oldInput.remove();
    }
    // Создаем новое поле с актуальными данными
    const cartInput = document.createElement('input');
    cartInput.type = 'hidden';
    cartInput.name = 'cart_data';
    cartInput.value = JSON.stringify(cart);
    form.appendChild(cartInput);
}

function updateQuantity(index, quantity) {
    const cart = JSON.parse(localStorage.getItem('cart') || '[]');
    if (cart[index]) {
        cart[index].quantity = parseInt(quantity) || 1;
        localStorage.setItem('cart', JSON.stringify(cart));
        loadCart();
        updateCartCount();
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatPrice(price) {
    return parseFloat(price).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
}
</script>

<?php include 'includes/footer.php'; ?>

