const paymentForm = document.querySelector('[data-payment-upload-form]');

if (paymentForm) {
    const fileInput = paymentForm.querySelector('input[name="payment_file"]');
    const preview = paymentForm.querySelector('[data-payment-preview]');
    const errorBox = paymentForm.querySelector('[data-payment-preview-error]');
    const submitButton = paymentForm.querySelector('[data-payment-submit]');

    const resetPreview = () => {
        preview.classList.add('hidden');
        errorBox.classList.add('hidden');
        errorBox.textContent = '';
        submitButton.disabled = true;
    };

    const showError = (message) => {
        preview.classList.add('hidden');
        errorBox.textContent = message;
        errorBox.classList.remove('hidden');
        submitButton.disabled = true;
    };

    fileInput.addEventListener('change', async () => {
        resetPreview();

        const [file] = fileInput.files;

        if (!file) {
            return;
        }

        if (!file.name.toLowerCase().endsWith('.10hf')) {
            showError('El archivo debe tener extension .10hf.');
            return;
        }

        submitButton.disabled = false;
    });
}
