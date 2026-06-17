    <footer class="footer">
        <span>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8'); ?></span>
    </footer>
</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?php echo base_url('assets/js/main.js') . '?v=' . filemtime(__DIR__ . '/../assets/js/main.js'); ?>"></script>
<script>
    (function () {
        if (window.__ktUiBound) {
            return;
        }
        var sidebar = document.getElementById('sidebar');
        var mainContent = document.getElementById('main-content');
        var toggle = document.querySelector('[data-toggle="sidebar"]');
        var profileButton = document.getElementById('profile-avatar-btn');
        var profileDropdown = document.getElementById('profile-dropdown');
        var isSidebarOpen = !window.matchMedia('(max-width: 768px)').matches;
        var isDropdownOpen = false;

        function setSidebarState() {
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
        }

        function setDropdownState() {
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
        }

        setSidebarState();
        setDropdownState();

        window.ktToggleSidebar = function () {
            isSidebarOpen = !isSidebarOpen;
            setSidebarState();
        };

        document.addEventListener('click', function (event) {
            var target = event.target;
            if (!(target instanceof Element)) {
                return;
            }

            if (toggle && target.closest && target.closest('[data-toggle="sidebar"]')) {
                window.ktToggleSidebar();
                return;
            }

            if (profileButton && target.closest && target.closest('#profile-avatar-btn')) {
                isDropdownOpen = !isDropdownOpen;
                setDropdownState();
                return;
            }

            if (isDropdownOpen && profileDropdown && profileButton) {
                if (!profileDropdown.contains(target) && !profileButton.contains(target)) {
                    isDropdownOpen = false;
                    setDropdownState();
                }
            }
        });
    })();
</script>
</body>
</html>
