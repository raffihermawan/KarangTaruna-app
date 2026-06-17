<?php
require_once __DIR__ . '/../helpers/auth-helper.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/query-helper.php';

$user = current_user();
$role = $user['role'] ?? '';

$pdo = db();
$settings = get_pengaturan($pdo);
$logoPath = $settings['logo'] ?? null;
$logoSrc = $logoPath ? base_url($logoPath) : base_url('assets/img/karang-taruna-logo.png');
?>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="brand-logo" id="sidebar-logo-container" aria-label="Logo Karang Taruna">
            <img id="sidebar-logo" src="<?php echo htmlspecialchars($logoSrc, ENT_QUOTES, 'UTF-8'); ?>" alt="Logo Karang Taruna">
        </div>
        <span class="brand-title">KARANG TARUNA 001 LEBAK BULUS</span>
        <span class="brand-role"><?php echo strtoupper(htmlspecialchars($role ?: 'guest', ENT_QUOTES, 'UTF-8')); ?></span>
    </div>
    <nav class="sidebar-nav">
        <a href="<?php echo base_url('pages/dashboard/index.php'); ?>">
            <span class="nav-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none"><path d="M4 13h6V4H4v9zm0 7h6v-5H4v5zm10 0h6V11h-6v9zm0-16v5h6V4h-6z" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </span>
            <span class="nav-text">Dashboard</span>
        </a>
        <a href="<?php echo base_url('pages/transaksi/index.php'); ?>">
            <span class="nav-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none"><path d="M7 7h10M7 7l3-3M7 7l3 3M17 17H7m10 0-3-3m3 3-3 3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </span>
            <span class="nav-text">Transaksi</span>
        </a>
        <?php if ($role === 'ketua') : ?>
            <a href="<?php echo base_url('pages/transaksi/persetujuan.php'); ?>">
                <span class="nav-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none"><path d="M9 12l2 2 4-4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/><path d="M7 4h10a2 2 0 0 1 2 2v12l-3-2-3 2-3-2-3 2-3-2V6a2 2 0 0 1 2-2z" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </span>
                <span class="nav-text">Persetujuan</span>
            </a>
        <?php endif; ?>
        <a href="<?php echo base_url('pages/kegiatan/index.php'); ?>">
            <span class="nav-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none"><path d="M7 4v3M17 4v3M4 9h16M6 12h4m-4 4h6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/><rect x="4" y="6" width="16" height="14" rx="2" stroke="currentColor" stroke-width="1.6"/></svg>
            </span>
            <span class="nav-text">Kegiatan</span>
        </a>
        <a href="<?php echo base_url('pages/laporan/index.php'); ?>">
            <span class="nav-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none"><path d="M7 4h7l4 4v12H7V4z" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/><path d="M14 4v4h4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </span>
            <span class="nav-text">Laporan</span>
        </a>
        <?php if (in_array($role, ['bendahara', 'ketua'], true)) : ?>
            <a href="<?php echo base_url('pages/anggota/index.php'); ?>">
                <span class="nav-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none"><path d="M8 14a4 4 0 1 1 8 0" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><path d="M6 20a6 6 0 0 1 12 0" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><circle cx="12" cy="8" r="3" stroke="currentColor" stroke-width="1.6"/></svg>
                </span>
                <span class="nav-text">Anggota</span>
            </a>
            <a href="<?php echo base_url('pages/kategori/index.php'); ?>">
                <span class="nav-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none"><path d="M5 12l7-7h7v7l-7 7-7-7z" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/><circle cx="17" cy="7" r="1.5" fill="currentColor"/></svg>
                </span>
                <span class="nav-text">Kategori</span>
            </a>
            <a href="<?php echo base_url('pages/user/index.php'); ?>">
                <span class="nav-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none"><path d="M15 20a3 3 0 0 0-6 0" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><circle cx="12" cy="8" r="3" stroke="currentColor" stroke-width="1.6"/><path d="M19 13h2m-1-1v2" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
                </span>
                <span class="nav-text">User</span>
            </a>
            <a href="<?php echo base_url('pages/pengaturan/index.php'); ?>">
                <span class="nav-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none"><path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6z" stroke="currentColor" stroke-width="1.6"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.9l.1.1-1.6 2.7-0.2-.1a2 2 0 0 0-2 .2l-.1.1-3.1-1.2v-.2a1.7 1.7 0 0 0-1.2-1.4h-.2L8.3 21l-0.1-.2a2 2 0 0 0-1.6-1l-.2-.1-1.6-2.7.1-.1a1.7 1.7 0 0 0 .3-1.9v-.2L2 12l3.2-1v-.2a1.7 1.7 0 0 0-.3-1.9l-.1-.1 1.6-2.7.2.1a2 2 0 0 0 2-.2l.1-.1 3.1 1.2v.2a1.7 1.7 0 0 0 1.2 1.4h.2L15.7 3l.1.2a2 2 0 0 0 1.6 1l.2.1 1.6 2.7-.1.1a1.7 1.7 0 0 0-.3 1.9v.2L22 12l-2.6 1z" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </span>
                <span class="nav-text">Pengaturan</span>
            </a>
        <?php endif; ?>
    </nav>
</aside>
    <div class="content" id="main-content">
    <header class="topbar">
        <button class="menu-toggle menu-toggle--icon" id="sidebar-toggle" type="button" data-toggle="sidebar" aria-label="Toggle menu" aria-expanded="true" onclick="if (window.ktToggleSidebar) { window.ktToggleSidebar(); }">
            <svg viewBox="0 0 24 24" fill="none"><path d="M4 7h16M4 12h16M4 17h10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
        </button>
        <div class="topbar-right">
            <span class="topbar-greeting">Selamat datang, <?php echo ucfirst(htmlspecialchars($role ?: 'pengguna', ENT_QUOTES, 'UTF-8')); ?>!</span>
            <div class="profile-menu">
                <button class="profile-button" id="profile-avatar-btn" type="button" aria-haspopup="true" aria-expanded="false">
                    <span class="profile-avatar" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none"><circle cx="12" cy="8" r="4" stroke="currentColor" stroke-width="1.6"/><path d="M4 20a8 8 0 0 1 16 0" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
                    </span>
                    <span class="profile-caret" aria-hidden="true">
                        <svg viewBox="0 0 16 16" fill="none"><path d="M4 6l4 4 4-4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                </button>
                <div class="profile-dropdown" id="profile-dropdown" role="menu">
                    <a href="<?php echo base_url('pages/profile/index.php'); ?>" class="profile-item" data-dropdown-close>Lihat Profil</a>
                    <a href="<?php echo base_url('pages/security/change-password.php'); ?>" class="profile-item" data-dropdown-close>Ganti Kata Sandi</a>
                    <a href="<?php echo base_url('pages/pengaturan/index.php'); ?>" class="profile-item" data-dropdown-close>Pengaturan Akun</a>
                    <a href="<?php echo base_url('pages/notifikasi/index.php'); ?>" class="profile-item" data-dropdown-close>Notifikasi</a>
                    <button type="button" class="profile-item profile-item--toggle" data-theme-toggle>
                        <span class="profile-item-left">
                            <span class="profile-item-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none"><path d="M20 15.5A8 8 0 1 1 8.5 4a6.5 6.5 0 1 0 11.5 11.5z" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </span>
                            <span>Mode Gelap</span>
                        </span>
                        <span class="toggle-switch" role="switch" aria-checked="false">
                            <span class="toggle-thumb"></span>
                        </span>
                    </button>
                    <a href="<?php echo base_url('auth/logout.php'); ?>"
                       class="profile-item profile-item--logout"
                       data-dropdown-close
                       data-confirm="Yakin ingin logout?"
                       data-confirm-title="Logout"
                       data-confirm-ok="Logout"
                       data-confirm-icon="warning">Logout</a>
                </div>
            </div>
        </div>
    </header>
