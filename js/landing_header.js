/**
 * Header landing: badge carrito desde localStorage.
 */
(function () {
    function updateCartBadge() {
        const badge = document.getElementById('cart-badge');
        if (!badge) return;
        let carrito = [];
        try {
            carrito = JSON.parse(localStorage.getItem('improgyp_carrito')) || [];
        } catch (e) {}
        let qty = 0;
        carrito.forEach((i) => { qty += i.cantidad || 0; });
        if (qty > 0) {
            badge.textContent = qty > 99 ? '99+' : String(qty);
            badge.classList.remove('hidden');
        } else {
            badge.classList.add('hidden');
        }
    }
    document.addEventListener('DOMContentLoaded', updateCartBadge);
    window.addEventListener('storage', updateCartBadge);
})();
