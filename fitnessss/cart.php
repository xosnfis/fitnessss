<?php
require_once 'config/config.php';
requireLogin();
$pageTitle = 'Корзина';
include 'includes/header.php';

// Получение корзины из localStorage через JavaScript будет обрабатываться на клиенте
?>

<h1>Корзина</h1>

<div id="cart-items">
    <div class="alert alert-info">Загрузка корзины...</div>
</div>

<div id="cart-summary" class="card mt-4" style="display: none;">
    <div class="card-body">
        <h5>Итого: <span id="total-amount" class="total-amount">0 ₽</span></h5>
        <form method="POST" action="checkout.php" id="checkout-form">
            <button type="submit" class="btn btn-success btn-lg">Оформить заказ</button>
            <button type="button" class="btn btn-secondary" onclick="clearCart(); location.reload();">Очистить корзину</button>
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
        cartItemsDiv.innerHTML = '<div class="alert alert-warning">Ваша корзина пуста</div>';
        cartSummary.style.display = 'none';
        return;
    }
    
    let html = '';
    let total = 0;
    
    cart.forEach((item, index) => {
        const subtotal = (item.price || 0) * (item.quantity || 1);
        total += subtotal;
        
        html += `
            <div class="cart-item card mb-2">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h5>${escapeHtml(item.name)}</h5>
                            <p class="text-muted mb-0">Тип: ${item.type === 'service' ? 'Услуга' : 'Абонемент'}</p>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Количество:</label>
                            <input type="number" class="form-control" value="${item.quantity || 1}" min="1" 
                                   onchange="updateQuantity(${index}, this.value)">
                        </div>
                        <div class="col-md-2 text-center">
                            <strong>${formatPrice(subtotal)} ₽</strong><br>
                            <small class="text-muted">${formatPrice(item.price)} ₽ за ед.</small>
                        </div>
                        <div class="col-md-2 text-end">
                            <button class="btn btn-danger btn-sm" onclick="removeFromCart('${item.type}', ${item.id})">
                                Удалить
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

