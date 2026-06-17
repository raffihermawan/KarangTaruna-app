function getConfirmOptions(element) {
    return {
        title: element.dataset.confirmTitle || 'Konfirmasi',
        text: element.dataset.confirm || 'Lanjutkan aksi ini?',
        icon: element.dataset.confirmIcon || 'warning',
        confirmText: element.dataset.confirmOk || 'Ya, lanjut',
        cancelText: element.dataset.confirmCancel || 'Batal',
    };
}

function showConfirm(options, onConfirm) {
    if (window.Swal && typeof window.Swal.fire === 'function') {
        window.Swal.fire({
            title: options.title,
            text: options.text,
            icon: options.icon,
            showCancelButton: true,
            confirmButtonText: options.confirmText,
            cancelButtonText: options.cancelText,
        }).then((result) => {
            if (result.isConfirmed) {
                onConfirm();
            }
        });
    } else if (confirm(options.text)) {
        onConfirm();
    }
}

function initDashboardUI() {
    const toggle = document.querySelector('[data-toggle="sidebar"]');
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('main-content');
    const profileButton = document.getElementById('profile-avatar-btn');
    const profileDropdown = document.getElementById('profile-dropdown');
    const themeToggle = document.querySelector('[data-theme-toggle]');
    const themeSwitch = themeToggle ? themeToggle.querySelector('.toggle-switch') : null;
    const body = document.body;
    const logoInput = document.getElementById('logo-input');
    const sidebarLogo = document.getElementById('sidebar-logo');
    const settingsForm = document.getElementById('form-pengaturan');
    const saveButton = document.getElementById('btn-simpan');

    let isSidebarOpen = !window.matchMedia('(max-width: 768px)').matches;
    let isDropdownOpen = false;
    let logoObjectUrl = null;
    const initialLogoSrc = sidebarLogo ? sidebarLogo.getAttribute('src') : null;

    const setSidebarState = () => {
        if (!sidebar) {
            return;
        }
        if (isSidebarOpen) {
            sidebar.classList.remove('is-collapsed');
            if (mainContent) {
                mainContent.classList.remove('is-expanded');
            }
        } else {
            sidebar.classList.add('is-collapsed');
            if (mainContent) {
                mainContent.classList.add('is-expanded');
            }
        }

        if (toggle) {
            toggle.setAttribute('aria-expanded', String(isSidebarOpen));
        }
    };

    const setDropdownState = () => {
        if (!profileDropdown) {
            return;
        }
        if (isDropdownOpen) {
            profileDropdown.classList.add('open');
            if (profileButton) {
                profileButton.setAttribute('aria-expanded', 'true');
            }
        } else {
            profileDropdown.classList.remove('open');
            if (profileButton) {
                profileButton.setAttribute('aria-expanded', 'false');
            }
        }
    };

    setSidebarState();
    setDropdownState();

    window.ktToggleSidebar = () => {
        isSidebarOpen = !isSidebarOpen;
        setSidebarState();
    };

    document.addEventListener('click', (event) => {
        const target = event.target;
        if (!(target instanceof Element)) {
            return;
        }

        const sidebarToggle = target.closest('[data-toggle="sidebar"]');
        if (sidebarToggle && sidebar) {
            window.ktToggleSidebar?.();
            return;
        }

        const profileToggle = target.closest('#profile-avatar-btn');
        if (profileToggle && profileButton) {
            isDropdownOpen = !isDropdownOpen;
            setDropdownState();
            return;
        }

        if (isDropdownOpen && profileDropdown && profileButton) {
            if (target.closest('[data-dropdown-close]')) {
                isDropdownOpen = false;
                setDropdownState();
                return;
            }
            if (!profileDropdown.contains(target) && !profileButton.contains(target)) {
                isDropdownOpen = false;
                setDropdownState();
            }
        }
    });

    const currentPath = window.location.pathname;
    document.querySelectorAll('.sidebar-nav a').forEach((link) => {
        try {
            const linkPath = new URL(link.href).pathname;
            if (linkPath === currentPath) {
                link.classList.add('active');
            }
        } catch (error) {
            console.warn(error);
        }
    });

    const storedTheme = localStorage.getItem('theme');
    if (storedTheme === 'dark') {
        body.classList.add('theme-dark');
        if (themeSwitch) {
            themeSwitch.setAttribute('aria-checked', 'true');
        }
    }

    if (themeToggle && themeSwitch) {
        themeToggle.addEventListener('click', () => {
            body.classList.toggle('theme-dark');
            const isDark = body.classList.contains('theme-dark');
            themeSwitch.setAttribute('aria-checked', isDark ? 'true' : 'false');
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
        });
    }

    if (logoInput && sidebarLogo) {
        logoInput.addEventListener('change', () => {
            const file = logoInput.files && logoInput.files[0] ? logoInput.files[0] : null;
            if (!file) {
                if (initialLogoSrc) {
                    sidebarLogo.src = initialLogoSrc;
                }
                return;
            }

            const validTypes = ['image/png', 'image/jpeg'];
            if (!validTypes.includes(file.type)) {
                if (window.Swal && typeof window.Swal.fire === 'function') {
                    window.Swal.fire({
                        icon: 'error',
                        title: 'Format tidak didukung',
                        text: 'Hanya file PNG atau JPG yang diperbolehkan.',
                    });
                } else {
                    alert('Hanya file PNG atau JPG yang diperbolehkan.');
                }
                logoInput.value = '';
                if (initialLogoSrc) {
                    sidebarLogo.src = initialLogoSrc;
                }
                return;
            }

            if (logoObjectUrl) {
                URL.revokeObjectURL(logoObjectUrl);
            }
            logoObjectUrl = URL.createObjectURL(file);
            sidebarLogo.src = logoObjectUrl;
        });
    }

    if (settingsForm && saveButton) {
        settingsForm.addEventListener('submit', () => {
            saveButton.dataset.label = saveButton.textContent || 'Simpan';
            saveButton.textContent = 'Menyimpan...';
            saveButton.classList.add('is-loading');
            saveButton.disabled = true;
        });
    }

    window.__ktUiBound = true;
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initDashboardUI);
} else {
    initDashboardUI();
}

document.addEventListener('click', (event) => {
    const target = event.target;
    if (!(target instanceof Element)) {
        return;
    }
    const link = target.closest ? target.closest('a[data-confirm]') : null;
    if (!link) {
        return;
    }

    event.preventDefault();
    const options = getConfirmOptions(link);
    showConfirm(options, () => {
        window.location.href = link.href;
    });
});

document.addEventListener('submit', (event) => {
    const form = event.target;
    if (!(form instanceof HTMLFormElement)) {
        return;
    }

    if (!form.dataset.confirm) {
        return;
    }

    if (form.dataset.confirmed === 'true') {
        return;
    }

    event.preventDefault();
    const options = getConfirmOptions(form);
    showConfirm(options, () => {
        form.dataset.confirmed = 'true';
        form.submit();
    });
});
