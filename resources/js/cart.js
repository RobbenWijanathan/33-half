import { showNotice } from './notices';

const cart = document.querySelector('[data-cart]');
if (cart) {
    let queue = Promise.resolve();

    cart.querySelectorAll('[data-quantity-form]').forEach((form) => {
        const input = form.elements.quantity;
        const row = form.closest('[data-cart-item]');
        let savedQuantity = input.value;
        let pendingQuantity = savedQuantity;
        let timer;

        function save() {
            clearTimeout(timer);
            if (!input.checkValidity() || input.value === pendingQuantity) return;

            const quantity = input.value;
            const body = new FormData(form);
            pendingQuantity = quantity;
            queue = queue.then(async () => {
                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: { Accept: 'application/json' },
                        body,
                    });

                    if (!response.ok) throw new Error('Quantity update failed');
                    const result = await response.json();

                    if (result.quantity === 0) row.remove();
                    savedQuantity = String(result.quantity);
                    if (pendingQuantity === quantity) {
                        pendingQuantity = savedQuantity;
                        if (input.value === quantity) input.value = savedQuantity;
                    }

                    row.querySelector('[data-line-total]').textContent = result.lineTotal;
                    document.querySelector('[data-cart-count]').textContent = result.quantityCount;
                    cart.querySelector('[data-cart-items-count]').textContent = result.itemCount === 1
                        ? '1 item on your shelf.'
                        : result.itemCount + ' items on your shelf.';
                    cart.querySelector('[data-cart-subtotal]').textContent = result.subtotal;
                    cart.querySelector('[data-cart-total]').textContent = result.total;
                    showNotice(result.quantity === 0 ? 'An unavailable item was removed.' : 'Cart updated.');
                    if (!result.itemCount) {
                        cart.querySelector('[data-cart-content]').hidden = true;
                        cart.querySelector('[data-cart-empty]').hidden = false;
                    }
                } catch {
                    if (pendingQuantity === quantity) {
                        pendingQuantity = savedQuantity;
                        if (input.value === quantity) input.value = savedQuantity;
                    }
                    showNotice('Could not update the cart. Please try again.', 'error');
                }
            });
        }

        input.addEventListener('input', () => {
            clearTimeout(timer);
            timer = setTimeout(save, 160);
        });
        input.addEventListener('change', () => {
            if (!input.checkValidity()) input.value = pendingQuantity;
            save();
        });
        form.addEventListener('submit', (event) => {
            event.preventDefault();
            save();
        });
    });
}
