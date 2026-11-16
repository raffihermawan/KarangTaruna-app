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

function stopMediaStream(stream) {
    if (!stream) {
        return;
    }
    stream.getTracks().forEach((track) => track.stop());
}

function createUploadPreview(file, preview, fileName, fallbackText) {
    if (!preview) {
        return null;
    }

    preview.innerHTML = '';

    if (!file) {
        const emptyState = document.createElement('div');
        emptyState.className = 'upload-preview__empty';
        emptyState.textContent = fallbackText || 'Belum ada file dipilih.';
        preview.appendChild(emptyState);
        if (fileName) {
            fileName.textContent = '';
        }
        return null;
    }

    const objectUrl = URL.createObjectURL(file);
    const isImage = file.type.startsWith('image/');
    const isPdf = file.type === 'application/pdf';

    if (isImage) {
        const image = document.createElement('img');
        image.src = objectUrl;
        image.alt = file.name;
        preview.appendChild(image);
    } else {
        const card = document.createElement('div');
        card.className = 'upload-preview__card';

        const icon = document.createElement('div');
        icon.className = 'upload-preview__icon';
        icon.textContent = isPdf ? 'PDF' : 'FILE';

        const meta = document.createElement('div');
        meta.className = 'upload-preview__meta';

        const title = document.createElement('strong');
        title.textContent = file.name;

        const desc = document.createElement('span');
        desc.textContent = isPdf ? 'Dokumen PDF siap diunggah.' : 'File siap diunggah.';

        meta.appendChild(title);
        meta.appendChild(desc);
        card.appendChild(icon);
        card.appendChild(meta);
        preview.appendChild(card);
    }

    if (fileName) {
        fileName.textContent = file.name;
    }

    return objectUrl;
}

function initUploadWidgets() {
    document.querySelectorAll('[data-upload-widget]').forEach((widget) => {
        const input = widget.querySelector('[data-upload-input]');
        const preview = widget.querySelector('[data-upload-preview]');
        const fileName = widget.querySelector('[data-upload-file-name]');
        const fileButton = widget.querySelector('[data-upload-file-trigger]');
        const cameraButton = widget.querySelector('[data-upload-camera]');
        const initialName = widget.getAttribute('data-upload-initial-name') || '';
        const initialType = widget.getAttribute('data-upload-initial-type') || '';
        const initialUrl = widget.getAttribute('data-upload-initial-url') || '';
        const fallbackText = 'Belum ada file dipilih.';

        if (!input || !preview) {
            return;
        }

        let previewObjectUrl = null;
        let cameraStream = null;
        let cameraDevices = [];
        let activeCameraIndex = -1;

        const renderInitialPreview = () => {
            preview.innerHTML = '';

            if (!initialUrl || !initialName) {
                const emptyState = document.createElement('div');
                emptyState.className = 'upload-preview__empty';
                emptyState.textContent = fallbackText;
                preview.appendChild(emptyState);
                if (fileName) {
                    fileName.textContent = '';
                }
                return;
            }

            if (initialType.startsWith('image/')) {
                const image = document.createElement('img');
                image.src = initialUrl;
                image.alt = initialName;
                preview.appendChild(image);
            } else {
                const card = document.createElement('div');
                card.className = 'upload-preview__card';

                const icon = document.createElement('div');
                icon.className = 'upload-preview__icon';
                icon.textContent = 'FILE';

                const meta = document.createElement('div');
                meta.className = 'upload-preview__meta';

                const title = document.createElement('strong');
                title.textContent = initialName;

                const desc = document.createElement('span');
                desc.textContent = 'Bukti lama tersimpan. Pilih file baru untuk mengganti.';

                meta.appendChild(title);
                meta.appendChild(desc);
                card.appendChild(icon);
                card.appendChild(meta);
                preview.appendChild(card);
            }

            if (fileName) {
                fileName.textContent = initialName;
            }
        };

        const renderSelectedFile = () => {
            if (previewObjectUrl) {
                URL.revokeObjectURL(previewObjectUrl);
                previewObjectUrl = null;
            }

            const file = input.files && input.files[0] ? input.files[0] : null;
            if (!file) {
                renderInitialPreview();
                return;
            }

            previewObjectUrl = createUploadPreview(file, preview, fileName, fallbackText);
        };

        const openCamera = async () => {
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                if (window.Swal && typeof window.Swal.fire === 'function') {
                    window.Swal.fire({
                        icon: 'info',
                        title: 'Kamera tidak tersedia',
                        text: 'Browser ini belum mendukung kamera, silakan pilih file gambar dari perangkat.',
                    });
                } else {
                    alert('Browser ini belum mendukung kamera, silakan pilih file gambar dari perangkat.');
                }
                input.click();
                return;
            }

            const dialog = document.createElement('dialog');
            dialog.className = 'camera-dialog';
            dialog.innerHTML = `
                <form method="dialog" class="camera-dialog__panel">
                    <div class="camera-dialog__header">
                        <div>
                            <div class="camera-dialog__title">Ambil Foto Bukti</div>
                            <div class="camera-dialog__hint">Arahkan kamera ke bukti transaksi, lalu ambil foto.</div>
                        </div>
                    </div>
                    <div class="camera-dialog__body">
                        <div class="camera-dialog__screen">
                            <video autoplay playsinline muted class="camera-dialog__video" data-camera-video></video>
                            <button type="button" class="camera-dialog__switch" data-camera-switch aria-label="Rotate kamera" title="Rotate kamera">
                                <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                    <path d="M17.65 6.35A7.95 7.95 0 0 0 12 4V1L7 6l5 5V7a5 5 0 1 1-5 5H5a7 7 0 1 0 12.65-5.65z"></path>
                                </svg>
                            </button>
                        </div>
                        <canvas class="camera-dialog__canvas" data-camera-canvas></canvas>
                    </div>
                    <div class="actions camera-dialog__actions">
                        <button type="button" class="btn btn-primary" data-camera-capture>Ambil Foto</button>
                        <button type="button" class="btn" data-camera-close>Batal</button>
                    </div>
                </form>
            `;

            document.body.appendChild(dialog);

            const video = dialog.querySelector('[data-camera-video]');
            const canvas = dialog.querySelector('[data-camera-canvas]');
            const closeButtons = dialog.querySelectorAll('[data-camera-close]');
            const captureButton = dialog.querySelector('[data-camera-capture]');
            const switchButton = dialog.querySelector('[data-camera-switch]');

            const stopCurrentStream = () => {
                stopMediaStream(cameraStream);
                cameraStream = null;
                if (video) {
                    video.srcObject = null;
                }
            };

            const refreshCameraDevices = async () => {
                const devices = await navigator.mediaDevices.enumerateDevices();
                cameraDevices = devices.filter((device) => device.kind === 'videoinput');
                if (switchButton) {
                    switchButton.disabled = cameraDevices.length < 2;
                    switchButton.style.visibility = cameraDevices.length < 2 ? 'hidden' : 'visible';
                }
                return cameraDevices;
            };

            const startCamera = async (deviceId = null) => {
                stopCurrentStream();

                const constraints = deviceId
                    ? { video: { deviceId: { exact: deviceId } }, audio: false }
                    : { video: { facingMode: { ideal: 'environment' } }, audio: false };

                cameraStream = await navigator.mediaDevices.getUserMedia(constraints);
                video.srcObject = cameraStream;
                if (typeof video.play === 'function') {
                    await video.play().catch(() => {});
                }
                await refreshCameraDevices();
                const activeTrack = cameraStream.getVideoTracks()[0];
                const activeDeviceId = activeTrack && activeTrack.getSettings ? activeTrack.getSettings().deviceId : '';
                activeCameraIndex = activeDeviceId
                    ? cameraDevices.findIndex((device) => device.deviceId === activeDeviceId)
                    : -1;
            };

            const switchCamera = async () => {
                if (cameraDevices.length < 2) {
                    return;
                }

                const nextIndex = activeCameraIndex >= 0
                    ? (activeCameraIndex + 1) % cameraDevices.length
                    : 0;

                activeCameraIndex = nextIndex;
                await startCamera(cameraDevices[nextIndex].deviceId);
            };

            try {
                await startCamera();
                if (typeof dialog.showModal === 'function') {
                    dialog.showModal();
                } else {
                    dialog.setAttribute('open', 'open');
                }
            } catch (error) {
                if (window.Swal && typeof window.Swal.fire === 'function') {
                    window.Swal.fire({
                        icon: 'error',
                        title: 'Kamera tidak bisa dibuka',
                        text: 'Izinkan akses kamera atau pilih file gambar langsung.',
                    });
                } else {
                    alert('Kamera tidak bisa dibuka. Izinkan akses kamera atau pilih file gambar langsung.');
                }
                dialog.remove();
                input.click();
                return;
            }

            const closeDialog = () => {
                stopCurrentStream();
                if (typeof dialog.close === 'function' && dialog.open) {
                    dialog.close();
                }
                dialog.remove();
            };

            closeButtons.forEach((button) => {
                button.addEventListener('click', closeDialog);
            });

            if (switchButton) {
                switchButton.addEventListener('click', () => {
                    switchCamera().catch(() => {
                        if (window.Swal && typeof window.Swal.fire === 'function') {
                            window.Swal.fire({
                                icon: 'error',
                                title: 'Kamera tidak bisa diganti',
                                text: 'Coba buka ulang kamera atau pilih file langsung.',
                            });
                        } else {
                            alert('Kamera tidak bisa diganti. Coba buka ulang kamera atau pilih file langsung.');
                        }
                    });
                });
            }

            captureButton.addEventListener('click', () => {
                if (!video.videoWidth || !video.videoHeight) {
                    return;
                }

                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                const context = canvas.getContext('2d');
                if (!context) {
                    return;
                }

                context.drawImage(video, 0, 0, canvas.width, canvas.height);
                canvas.toBlob((blob) => {
                    if (!blob) {
                        return;
                    }

                    const capturedFile = new File([blob], `bukti-${Date.now()}.jpg`, { type: 'image/jpeg' });
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(capturedFile);
                    input.files = dataTransfer.files;
                    renderSelectedFile();
                    closeDialog();
                }, 'image/jpeg', 0.92);
            });

            dialog.addEventListener('close', closeDialog, { once: true });
            dialog.addEventListener('cancel', closeDialog, { once: true });
        };

        input.addEventListener('change', renderSelectedFile);

        if (fileButton) {
            fileButton.addEventListener('click', () => input.click());
        }

        if (cameraButton) {
            cameraButton.addEventListener('click', () => {
                openCamera().catch(() => {
                    input.click();
                });
            });
        }

        renderInitialPreview();
    });
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

    initUploadWidgets();

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
