const checkout = document.querySelector('[data-checkout]');
if (checkout) {
    const fields = checkout.querySelector('[data-delivery-fields]');
    const credit = checkout.elements.credit_amount;
    const balance = Number(checkout.dataset.balance);
    const subtotal = Number(checkout.dataset.subtotal);
    const money = (amount) => checkout.dataset.currency === 'USD'
        ? '$' + (amount / Number(checkout.dataset.rate)).toFixed(2)
        : 'Rp ' + Math.round(amount).toLocaleString('id-ID');

    function updateSummary() {
        const delivery = checkout.elements.fulfillment.value === 'delivery';
        const fee = delivery ? Number(checkout.dataset.fee) : 0;
        fields.hidden = !delivery;
        fields.querySelectorAll('input').forEach((input) => { input.required = delivery; });
        credit.max = Math.min(balance, subtotal + fee);
        const applied = Math.min(Math.max(0, Number(credit.value) || 0), Number(credit.max));
        checkout.querySelector('[data-delivery-total]').textContent = money(fee);
        checkout.querySelector('[data-credit-total]').textContent = '−' + money(applied);
        checkout.querySelector('[data-credit-remaining]').textContent = money(balance - applied);
        checkout.querySelector('[data-checkout-total]').textContent = money(subtotal + fee - applied);
    }

    const saved = checkout.querySelector('[data-saved-address]');
    function fillAddress() {
        const address = saved.selectedOptions[0].dataset.address;
        if (address) {
            Object.entries(JSON.parse(address)).forEach(([name, value]) => { checkout.elements[name].value = value; });
        }
    }
    saved.addEventListener('change', fillAddress);
    fields.querySelectorAll('input').forEach((input) => input.addEventListener('input', () => { saved.value = ''; }));
    checkout.addEventListener('input', updateSummary);
    checkout.addEventListener('change', updateSummary);
    fillAddress();
    updateSummary();
}

let pendingForm;
document.querySelectorAll('[data-product-variant]').forEach((select) => {
    function selectVariant() {
        const option = select.selectedOptions[0];
        if (!option) return;
        const form = select.form;
        form.elements.quantity.max = Math.max(1, Number(option.dataset.stock));
        form.elements.quantity.value = Math.min(Number(form.elements.quantity.value), Number(form.elements.quantity.max));
        const price = form.closest('.detail-info')?.querySelector('.detail-price');
        if (price) price.textContent = option.dataset.price;
    }
    select.addEventListener('change', selectVariant);
    selectVariant();
});
document.querySelectorAll('form[data-confirm]').forEach((form) => {
    form.addEventListener('submit', (event) => {
        if (form.dataset.confirmed === 'yes') return;
        if (!window.bootstrap) {
            if (!window.confirm(form.dataset.confirm)) event.preventDefault();
            return;
        }
        event.preventDefault();
        pendingForm = form;
        const modal = document.getElementById('confirm-action');
        modal.querySelector('[data-confirm-message]').textContent = form.dataset.confirm;
        bootstrap.Modal.getOrCreateInstance(modal).show();
    });
});
document.querySelector('[data-confirm-continue]')?.addEventListener('click', () => {
    if (!pendingForm) return;
    pendingForm.dataset.confirmed = 'yes';
    pendingForm.requestSubmit();
});

document.querySelectorAll('[data-image-preview]').forEach((input) => {
    const preview = document.createElement('div');
    preview.className = 'd-flex flex-wrap gap-2 my-3';
    input.after(preview);
    let urls = [];
    input.addEventListener('change', () => {
        urls.forEach((url) => URL.revokeObjectURL(url));
        urls = [];
        preview.replaceChildren();
        [...input.files].slice(0, 6).filter((file) => file.type.startsWith('image/')).forEach((file) => {
            const image = document.createElement('img');
            image.src = URL.createObjectURL(file);
            urls.push(image.src);
            image.alt = 'Selected upload: ' + file.name;
            image.className = 'upload-preview';
            preview.append(image);
        });
    });
});

document.querySelector('[data-add-variant]')?.addEventListener('click', () => {
    const rows = document.querySelector('[data-variant-rows]');
    if (rows.children.length >= 30) return;
    const row = rows.lastElementChild.cloneNode(true);
    const index = rows.children.length;
    row.querySelectorAll('input').forEach((input) => {
        input.name = input.name.replace(/variants\[\d+\]/, 'variants[' + index + ']');
        input.value = input.type === 'checkbox' ? '1' : '';
        input.checked = false;
        if (input.hasAttribute('aria-label')) input.setAttribute('aria-label', input.getAttribute('aria-label').replace(/\d+$/, String(index + 1)));
    });
    rows.append(row);
    row.querySelector('input:not([type="hidden"])').focus();
});

document.querySelector('[data-checklist-search]')?.addEventListener('input', (event) => {
    const search = event.target.value.toLowerCase();
    document.querySelectorAll('[data-checklist] label').forEach((label) => { label.hidden = !label.textContent.toLowerCase().includes(search); });
});

document.querySelectorAll('form').forEach((form) => {
    form.addEventListener('submit', (event) => {
        if (event.defaultPrevented) return;
        const button = form.querySelector('[data-loading-text]');
        if (button) {
            button.disabled = true;
            button.textContent = button.dataset.loadingText;
        }
    });
});
