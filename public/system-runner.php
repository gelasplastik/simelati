<?php

declare(strict_types=1);

use Illuminate\Contracts\Console\Kernel;

header('Content-Type: application/json; charset=utf-8');

$token = $_GET['token'] ?? '';
$action = $_GET['action'] ?? '';

$basePath = dirname(__DIR__);

if (! is_file($basePath.'/vendor/autoload.php')) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'message' => 'vendor/autoload.php tidak ditemukan. Upload vendor atau jalankan composer install dulu.',
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

require $basePath.'/vendor/autoload.php';

$app = require_once $basePath.'/bootstrap/app.php';
/** @var Kernel $kernel */
$kernel = $app->make(Kernel::class);

$expectedToken = (string) env('SYSTEM_RUNNER_TOKEN', '');
$allowedIp = (string) env('SYSTEM_RUNNER_ALLOWED_IP', '');

if ($expectedToken === '') {
    http_response_code(403);
    echo json_encode([
        'ok' => false,
        'message' => 'SYSTEM_RUNNER_TOKEN belum diset di .env',
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

if (! hash_equals($expectedToken, (string) $token)) {
    http_response_code(403);
    echo json_encode([
        'ok' => false,
        'message' => 'Token tidak valid.',
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

if ($allowedIp !== '') {
    $clientIp = $_SERVER['REMOTE_ADDR'] ?? '';
    if ($clientIp !== $allowedIp) {
        http_response_code(403);
        echo json_encode([
            'ok' => false,
            'message' => 'IP tidak diizinkan.',
            'client_ip' => $clientIp,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }
}

$commandsByAction = [
    'optimize-clear' => [
        ['optimize:clear', []],
    ],
    'migrate' => [
        ['migrate', ['--force' => true]],
    ],
    'migrate-optimize' => [
        ['optimize:clear', []],
        ['migrate', ['--force' => true]],
    ],
    'full' => [
        ['optimize:clear', []],
        ['migrate', ['--force' => true]],
        ['db:seed', ['--force' => true]],
    ],
];

if (! isset($commandsByAction[$action])) {
    http_response_code(400);
    echo json_encode([
        'ok' => false,
        'message' => 'Action tidak valid.',
        'allowed_actions' => array_keys($commandsByAction),
        'example' => '/system-runner.php?action=migrate-optimize&token=YOUR_TOKEN',
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

$results = [];
$allOk = true;

foreach ($commandsByAction[$action] as [$command, $arguments]) {
    try {
        $exitCode = $kernel->call($command, $arguments);
        $output = trim($kernel->output());

        $results[] = [
            'command' => $command,
            'arguments' => $arguments,
            'exit_code' => $exitCode,
            'output' => $output,
            'status' => $exitCode === 0 ? 'success' : 'failed',
        ];

        if ($exitCode !== 0) {
            $allOk = false;
            break;
        }
    } catch (Throwable $e) {
        $allOk = false;
        $results[] = [
            'command' => $command,
            'arguments' => $arguments,
            'status' => 'exception',
            'error' => $e->getMessage(),
        ];
        break;
    }
}

$kernel->terminate(request(), response());

http_response_code($allOk ? 200 : 500);
echo json_encode([
    'ok' => $allOk,
    'action' => $action,
    'results' => $results,
    'security_notice' => 'Hapus file public/system-runner.php setelah maintenance selesai.',
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);