<?php
require_once __DIR__ . '/../config/session.php';

function is_logged_in(): bool
{
    return !empty($_SESSION['user']);
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function require_login(): void
{
    if (!is_logged_in()) {
        header('Location: ' . base_url('auth/login.php'));
        exit;
    }
}

function require_role($roles): void
{
    require_login();

    $user = current_user();
    $role = $user['role'] ?? '';
    $allowed = is_array($roles) ? $roles : [$roles];

    if (!in_array($role, $allowed, true)) {
        http_response_code(403);
        exit('Akses ditolak.');
    }
}

function set_flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}
