<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Controller;

use jschreuder\Middle\Controller\ControllerInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class NotFoundHandlerController implements ControllerInterface
{
    public function execute(ServerRequestInterface $request) : ResponseInterface
    {
        return new JsonResponse(
            [
                'message' => 'Not found: ' . $request->getUri()->getPath(),
            ],
            404
        );
    }
}
