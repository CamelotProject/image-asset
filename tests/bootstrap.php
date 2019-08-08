<?php

declare(strict_types=1);

use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Filesystem\Filesystem;

require __DIR__ . '/../vendor/autoload.php';

if (is_array($env = @include __DIR__ . '/../.env.local.php')) {
    foreach ($env as $k => $v) {
        $_ENV[$k] = $_ENV[$k] ?? (isset($_SERVER[$k]) && strpos($k, 'HTTP_') !== 0 ? $_SERVER[$k] : $v);
    }
} elseif (!class_exists(Dotenv::class)) {
    throw new RuntimeException('Please run "composer require symfony/dotenv" to load the ".env" files configuring the application.');
} else {
    // load all the .env files
    (new Dotenv(false))->loadEnv(__DIR__ . '/../.env');
}

$_SERVER += $_ENV;
$_SERVER['APP_ENV'] = $_ENV['APP_ENV'] = ($_SERVER['APP_ENV'] ?? $_ENV['APP_ENV'] ?? null) ?: 'dev';
$_SERVER['APP_DEBUG'] = $_SERVER['APP_DEBUG'] ?? $_ENV['APP_DEBUG'] ?? $_SERVER['APP_ENV'] !== 'prod';
$_SERVER['APP_DEBUG'] = $_ENV['APP_DEBUG'] = (int) $_SERVER['APP_DEBUG'] || filter_var($_SERVER['APP_DEBUG'], FILTER_VALIDATE_BOOLEAN) ? '1' : '0';
$_SERVER['APP_SAVE_PATH'] = dirname(__DIR__) . DIRECTORY_SEPARATOR . ($_ENV['APP_SAVE_PATH'] = ($_SERVER['APP_SAVE_PATH'] ?? $_ENV['APP_SAVE_PATH'] ?? null) ?: 'tmp');

$assetCheck = function (): void {
    $fixtureDir = (realpath(__DIR__ . '/Fixtures/App'));
    $assetDir = $fixtureDir . '/public/bundles/camelotimageasset';
    $fs = new Filesystem();
    if (!$fs->exists($assetDir)) {
        throw new RuntimeException(sprintf('Fixture web assets have not been installed in %s%s +++ HINT: php %s/bin/console assets:install', $assetDir, PHP_EOL, $fixtureDir));
    }
};
$assetCheck();
