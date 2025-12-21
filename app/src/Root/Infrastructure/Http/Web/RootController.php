<?php

namespace App\Root\Infrastructure\Http\Web;

use CodersLairDev\ClFw\Http\Response\Trait\ResponseTrait;
use CodersLairDev\ClFw\Routing\Attribute\AsController;
use CodersLairDev\ClFw\Routing\Attribute\AsRoute;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\ResponseInterface;

#[AsController]
class RootController
{
    use ResponseTrait;

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
}