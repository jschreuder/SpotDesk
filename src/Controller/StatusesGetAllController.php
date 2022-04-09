<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Controller;

use jschreuder\Middle\Controller\ControllerInterface;
use jschreuder\SpotDesk\Entity\Status;
use jschreuder\SpotDesk\Repository\StatusRepository;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class StatusesGetAllController implements ControllerInterface
{
    public function __construct(private StatusRepository $statusRepository)
    {
    }

    public function execute(ServerRequestInterface $request) : ResponseInterface
    {
        $statuses = $this->statusRepository->getStatuses();

        return new JsonResponse([
            'statuses' => array_map(function (Status $status) {
                return [
                    'name' => $status->getName(),
                    'status_type' => $status->getType()->toString(),
                ];
            }, $statuses->toArray())
        ], 200);
    }
}
