function formatPhone(value) {
    const digits = value.replace(/\D/g, '').slice(0, 15);

    if (digits.length <= 2) {
        return digits.length ? `(${digits}` : '';
    }

    if (digits.length <= 6) {
        return `(${digits.slice(0, 2)}) ${digits.slice(2)}`;
    }

    if (digits.length <= 10) {
        return `(${digits.slice(0, 2)}) ${digits.slice(2, 6)}-${digits.slice(6)}`;
    }

    if (digits.length === 11) {
        return `(${digits.slice(0, 2)}) ${digits.slice(2, 7)}-${digits.slice(7)}`;
    }

    return `+${digits}`;
}

document.querySelectorAll('[data-phone-input]').forEach((input) => {
    input.value = formatPhone(input.value);
    input.addEventListener('input', () => {
        input.value = formatPhone(input.value);
    });
});

document.querySelectorAll('[data-phone-display]').forEach((element) => {
    element.textContent = formatPhone(element.textContent);
});

document.querySelectorAll('[data-photo-input]').forEach((input) => {
    input.addEventListener('change', () => {
        const file = input.files?.[0];
        const preview = document.querySelector('[data-photo-preview]');

        if (!file || !preview) {
            return;
        }

        preview.src = URL.createObjectURL(file);
        preview.classList.remove('hidden');
        preview.addEventListener('load', () => URL.revokeObjectURL(preview.src), { once: true });
    });
});

document.querySelectorAll('[data-image-fallback]').forEach((image) => {
    if (image.complete && image.naturalWidth === 0) {
        image.classList.add('hidden');
    }

    image.addEventListener('error', () => image.classList.add('hidden'), { once: true });
});

const deleteModal = document.querySelector('[data-delete-modal]');

if (deleteModal) {
    const customerName = deleteModal.querySelector('[data-delete-customer-name]');
    const cancelButton = deleteModal.querySelector('[data-delete-cancel]');
    const confirmButton = deleteModal.querySelector('[data-delete-confirm]');
    let activeForm = null;
    let triggerButton = null;

    document.querySelectorAll('[data-delete-trigger]').forEach((button) => {
        button.addEventListener('click', () => {
            activeForm = button.closest('[data-delete-form]');
            triggerButton = button;
            customerName.textContent = button.dataset.customerName;
            deleteModal.showModal();
            cancelButton.focus();
        });
    });

    cancelButton.addEventListener('click', () => deleteModal.close());

    confirmButton.addEventListener('click', () => {
        if (activeForm) {
            activeForm.requestSubmit();
        }
    });

    deleteModal.addEventListener('click', (event) => {
        if (event.target === deleteModal) {
            deleteModal.close();
        }
    });

    deleteModal.addEventListener('close', () => {
        triggerButton?.focus();
        activeForm = null;
        triggerButton = null;
    });
}
