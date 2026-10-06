// The cart is session backed; this only clarifies when an edited quantity needs saving.
document.querySelectorAll('.cart-item-actions input[name="quantity"]').forEach((input) => {
    input.addEventListener('change', () => {
        const button = input.closest('form')?.querySelector('button[type="submit"]');
        if (button) button.textContent = 'Save quantity';
    });
});
