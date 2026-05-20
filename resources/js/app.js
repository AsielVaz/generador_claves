const paymentForm = document.querySelector('[data-payment-upload-form]');

const moneyInputs = document.querySelectorAll('[data-money-input]');

const cleanMoneyValue = (value) => value.replace(/,/g, '').replace(/[^\d.]/g, '');

const formatMoneyValue = (value) => {
    const cleaned = cleanMoneyValue(value);

    if (!cleaned) {
        return '';
    }

    const [integerPart, ...decimalParts] = cleaned.split('.');
    const decimals = decimalParts.join('').slice(0, 2);
    const integer = integerPart.replace(/^0+(?=\d)/, '') || '0';
    const formattedInteger = Number(integer).toLocaleString('en-US');

    return decimals.length > 0 || cleaned.includes('.')
        ? `${formattedInteger}.${decimals}`
        : formattedInteger;
};

moneyInputs.forEach((input) => {
    input.value = formatMoneyValue(input.value);

    input.addEventListener('input', () => {
        input.value = formatMoneyValue(input.value);
    });

    input.form?.addEventListener('submit', () => {
        input.value = cleanMoneyValue(input.value);
    });
});

if (paymentForm) {
    const fileInput = paymentForm.querySelector('input[name="payment_file"]');
    const preview = paymentForm.querySelector('[data-payment-preview]');
    const errorBox = paymentForm.querySelector('[data-payment-preview-error]');
    const submitButton = paymentForm.querySelector('[data-payment-submit]');
    const previewUrl = paymentForm.dataset.paymentPreviewUrl;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

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

    const showPreview = (payment) => {
        const amount = Number(payment.amount);

        setText('amount', amount.toLocaleString('es-MX', { style: 'currency', currency: 'MXN' }));
        setText('method', payment.method);
        setText('status', payment.status);
        setText('paid_at', payment.paid_at || 'Sin fecha');
        setText('reference', payment.reference || 'Sin referencia');
        setText('unica', payment.unica);

        errorBox.classList.add('hidden');
        preview.classList.remove('hidden');
        submitButton.disabled = false;
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

        const formData = new FormData();
        formData.append('payment_file', file);

        try {
            const response = await fetch(previewUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: formData,
            });
            const data = await response.json();

            if (!response.ok) {
                let message = data.message || 'No se pudo previsualizar el archivo 10hf.';

                if (data.decrypted_content) {
                    message = `${message}\n\nCadena desencriptada:\n${data.decrypted_content}`;
                }

                showError(message);
                return;
            }

            showPreview(data.payment);
        } catch (error) {
            showError('No se pudo previsualizar el archivo 10hf.');
        }
    });
}

const passwordToggle = document.querySelector('[data-password-toggle]');

if (passwordToggle) {
    const passwordFields = document.querySelectorAll('[data-password-field]');

    passwordToggle.addEventListener('click', () => {
        const shouldShow = Array.from(passwordFields).some((field) => field.type === 'password');

        passwordFields.forEach((field) => {
            field.type = shouldShow ? 'text' : 'password';
        });

        passwordToggle.textContent = shouldShow ? 'Ocultar contrasenas' : 'Ver contrasenas';
    });
}
