<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Controller;

use jschreuder\Middle\Controller\ControllerInterface;
use jschreuder\SpotDesk\Entity\Status;
use jschreuder\SpotDesk\Repository\StatusRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;

class StatusesGetAllController implements ControllerInterface
{
    /** @var  StatusRepository */
    private $statusRepository;

    public function __construct(StatusRepository $statusRepository)
    {
        $this->statusRepository = $statusRepository;
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
