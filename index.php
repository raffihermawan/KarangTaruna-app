<?php
require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/helpers/auth-helper.php';

if (is_logged_in()) {
    header('Location: ' . base_url('pages/dashboard/index.php'));
    exit;
}

header('Location: ' . base_url('auth/login.php'));
exit;
