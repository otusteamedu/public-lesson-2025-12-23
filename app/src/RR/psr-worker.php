<?php

require '/app/vendor/autoload.php';

use CodersLairDev\ClFw\Kernel\Kernel;
use Nyholm\Psr7\Response;
use Nyholm\Psr7\Factory\Psr17Factory;
use Spiral\RoadRunner\Http\PSR7Worker;


$worker = \Spiral\RoadRunner\Worker::create();

$factory = new Psr17Factory();

$psr7 = new PSR7Worker($worker, $factory, $factory, $factory);

$projectDir = dirname(__DIR__);

$config = [
    'services' => [
        [
            'path' => 'Root',
            'namespace' => 'App\Root',
        ],
        [
            'path' => 'Pdf',
            'namespace' => 'App\Pdf',
        ],
    ]
];

$kernel = new Kernel($projectDir, $config);

while (true) {
    try {
        $request = $psr7->waitRequest();

        if ($request === null) {
            break;
        }
    } catch (Throwable $e) {
        $psr7->respond(new Response(400));
        continue;
    }

    try {
        $response = $kernel->handle($request, true);
        $psr7->respond($response);
    } catch (Throwable $e) {
        $psr7->respond(new Response(500, [], 'Something Went Wrong!'));
        $psr7->getWorker()->error((string)$e);
    }
}