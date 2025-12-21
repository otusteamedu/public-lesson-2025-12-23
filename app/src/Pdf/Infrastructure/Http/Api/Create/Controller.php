<?php

namespace App\Pdf\Infrastructure\Http\Api\Create;

use App\Pdf\Domain\Service\PdfService;
use CodersLairDev\ClFw\Http\Response\Trait\ResponseTrait;
use CodersLairDev\ClFw\Routing\Attribute\AsController;
use CodersLairDev\ClFw\Routing\Attribute\AsRoute;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

#[AsController]
class Controller
{
    use ResponseTrait;

    private LoggerInterface $logger;

    public function __construct()
    {
        $this->logger = new Logger('pdf-create');
        $this->logger->pushHandler(new StreamHandler('/app/var/log/pdf-create.log'));
    }

    #[AsRoute(path: '/api/pdf/create')]
    public function createPdf(ServerRequestInterface $request, PdfService $pdfService): ResponseInterface
    {
        $this->logger->info('createPdf BEGIN', [
            'timeStart' => microtime(true)
        ]);

        $params = json_decode($request->getBody()->getContents(), true);

        $file = $pdfService->createPdf($params);

        $this->logger->info('createPdf BEGIN', [
            'time_created' => microtime(true)
        ]);

        return $this->createJsonResponse(
            psr17Factory: new Psr17Factory(),
            content: ['file' => $file],
            status: 200
        );
    }
}