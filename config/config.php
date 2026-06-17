<?php
define('APP_NAME', 'Karang Taruna 001 Lebak Bulus');
define('APP_TIMEZONE', 'Asia/Jakarta');

$autoloadPath = __DIR__ . '/../vendor/autoload.php';
if (is_file($autoloadPath)) {
    require_once $autoloadPath;

    $envPath = dirname(__DIR__);
    if (class_exists('Dotenv\\Dotenv') && is_file($envPath . '/.env')) {
        $dotenv = Dotenv\Dotenv::createImmutable($envPath);
        $dotenv->safeLoad();
    }
}

date_default_timezone_set(APP_TIMEZONE);

function base_url(string $path = ''): string
{
    $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    $protocol = $https ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

    $docRoot = str_replace('\\', '/', rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/'));
    $appRoot = str_replace('\\', '/', dirname(__DIR__));
    $relative = $docRoot && str_starts_with($appRoot, $docRoot)
        ? substr($appRoot, strlen($docRoot))
        : dirname($_SERVER['SCRIPT_NAME'] ?? '');
    $relative = '/' . trim(str_replace('\\', '/', $relative), '/');
    if ($relative === '/') {
        $relative = '';
    }

    $base = rtrim($protocol . '://' . $host . $relative, '/');

    if ($path === '') {
        return $base;
    }

    return $base . '/' . ltrim($path, '/');
}
