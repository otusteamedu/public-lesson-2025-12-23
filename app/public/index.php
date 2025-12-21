<?php

declare(strict_types=1);

use CodersLairDev\ClFw\Kernel\Kernel;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

require_once dirname(__DIR__) . '/vendor/autoload.php';

$projectDir = dirname(__DIR__);

$indexLogFile = $projectDir . '/var/log/index.log';

$config = [
    'services' => [
        [
            'path' => 'src/Root',
            'namespace' => 'App\Root',
        ],
        [
            'path' => 'src/Pdf',
            'namespace' => 'App\Pdf',
        ],
    ]
];

$timeStart = microtime(true);

$kernel = new Kernel($projectDir, $config);

$timeKernel = microtime(true);

$kernel->handle();

$timeHandled = microtime(true);

$logger = new Logger('index');
$logger->pushHandler(new StreamHandler($indexLogFile));

$logger->info('MEMORY USAGE', [
    'timeStart' => $timeStart,
    'timeKernel' => $timeKernel,
    'timeHandled' => $timeHandled,
]);