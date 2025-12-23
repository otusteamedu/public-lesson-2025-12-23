<?php

namespace App\Root\Infrastructure\Http\Web;

use CodersLairDev\ClFw\Http\Response\Trait\ResponseTrait;
use CodersLairDev\ClFw\Routing\Attribute\AsController;
use CodersLairDev\ClFw\Routing\Attribute\AsRoute;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

#[AsController]
class RootController
{
    use ResponseTrait;

    private LoggerInterface $logger;

    public function __construct()
    {
        $this->logger = new Logger('sha1_log');
        $this->logger->pushHandler(new StreamHandler('/app/var/log/sha1_log.log'));
    }

    #[AsRoute(path: '/')]
    public function rootIndex(): ResponseInterface
    {
        $data = [
            'success' => true,
            'data' => __CLASS__ . '::' . __FUNCTION__ . '()',
        ];

        return $this->createResponse(
            psr17Factory: new Psr17Factory(),
            content: json_encode($data),
            status: 200
        );
    }

    #[AsRoute(path: '/getSha1Hash')]
    public function getSha1Hash():ResponseInterface
    {
        $requestId = uniqid();
        $requestSha1Hash = sha1($requestId);

        $this->logger->info('getSha1Hash', [
            'requestId' => $requestId,
            'requestSha1Hash' => $requestSha1Hash,
        ]);

        return $this->createResponse(
            psr17Factory: new Psr17Factory(),
            content: json_encode([
                'requestId' => $requestId,
                'requestSha1Hash' => $requestSha1Hash,
            ]),
            status: 200
        );
    }
}