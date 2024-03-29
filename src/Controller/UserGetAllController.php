<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Controller;

use jschreuder\Middle\Controller\ControllerInterface;
use jschreuder\SpotDesk\Entity\User;
use jschreuder\SpotDesk\Repository\UserRepository;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class UserGetAllController implements ControllerInterface
{
    public function __construct(private UserRepository $userRepository)
    {
    }

    public function execute(ServerRequestInterface $request) : ResponseInterface
    {
        $users = $this->userRepository->getUsers();

        return new JsonResponse([
            'users' => array_map(function (User $user) {
                return [
                    'email' => $user->getEmail()->toString(),
                    'display_name' => $user->getDisplayName(),
                    'role' => $user->getRole()->getName(),
                    'active' => $user->isActive(),
                ];
            }, $users->toArray())
        ], 200);
    }
}
