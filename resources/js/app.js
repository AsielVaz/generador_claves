const paymentForm = document.querySelector('[data-payment-upload-form]');

if (paymentForm) {
    const fileInput = paymentForm.querySelector('input[name="payment_file"]');
    const preview = paymentForm.querySelector('[data-payment-preview]');
    const errorBox = paymentForm.querySelector('[data-payment-preview-error]');
    const submitButton = paymentForm.querySelector('[data-payment-submit]');

    const setText = (field, value) => {
        const target = paymentForm.querySelector(`[data-preview-field="${field}"]`);
        if (target) {
            target.textContent = value || 'Sin referencia';
        }
    };

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

        try {
            const data = JSON.parse(await file.text());
            const requiredFields = ['amount', 'method', 'status', 'unica'];
            const missingField = requiredFields.find((field) => data[field] === undefined || data[field] === null || data[field] === '');

            if (missingField) {
                showError('El JSON no tiene la estructura requerida para registrar el pago.');
                return;
            }

            const amount = Number(data.amount);

            if (!Number.isFinite(amount) || amount <= 0) {
                showError('El monto del JSON debe ser un numero mayor a cero.');
                return;
            }

            setText('amount', amount.toLocaleString('es-MX', { style: 'currency', currency: 'MXN' }));
            setText('method', data.method);
            setText('status', data.status);
            setText('paid_at', data.paid_at || 'Sin fecha');
            setText('reference', data.reference || 'Sin referencia');
            setText('unica', data.unica);

            errorBox.classList.add('hidden');
            preview.classList.remove('hidden');
            submitButton.disabled = false;
        } catch (error) {
            showError('No se pudo leer el archivo. Verifica que contenga un JSON valido.');
        }
    });
}
