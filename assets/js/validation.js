document.addEventListener('submit', (event) => {
    const form = event.target;
    if (!form.matches('form[data-validate]')) {
        return;
    }

    const requiredFields = form.querySelectorAll('[required]');
    for (const field of requiredFields) {
        if (!field.value.trim()) {
            event.preventDefault();
            alert('Mohon lengkapi semua field yang wajib diisi.');
            field.focus();
            break;
        }
    }
});
