<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Controller;

use jschreuder\Middle\Controller\ControllerInterface;
use jschreuder\SpotDesk\Entity\User;
use jschreuder\SpotDesk\Repository\UserRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;

class GetUsersController implements ControllerInterface
{
    /** @var  UserRepository */
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function execute(ServerRequestInterface $request): ResponseInterface
    {
        $users = $this->userRepository->getUsers();

        return new JsonResponse([
            'users' => array_map(function (User $user) {
                return [
                    'email' => $user->getEmail()->toString(),
                    'display_name' => $user->getDisplayName(),
                ];
            }, $users->toArray())
        ], 200);
    }
}
