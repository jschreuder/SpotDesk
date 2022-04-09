<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Controller;

use jschreuder\Middle\Controller\ControllerInterface;
use jschreuder\Middle\Controller\RequestFilterInterface;
use jschreuder\Middle\Controller\RequestValidatorInterface;
use jschreuder\SpotDesk\Collection\DepartmentCollection;
use jschreuder\SpotDesk\Entity\Department;
use jschreuder\SpotDesk\Entity\Mailbox;
use jschreuder\SpotDesk\Entity\User;
use jschreuder\SpotDesk\Repository\DepartmentRepository;
use jschreuder\SpotDesk\Repository\MailboxRepository;
use jschreuder\SpotDesk\Repository\UserRepository;
use jschreuder\SpotDesk\Service\ValidationService;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Validator\Uuid as UuidValidator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;

class DepartmentGetOneController implements ControllerInterface, RequestFilterInterface, RequestValidatorInterface
{
    public function __construct(
        private DepartmentRepository $departmentRepository,
        private UserRepository $userRepository,
        private MailboxRepository $mailboxRepository
    )
    {
    }

    public function filterRequest(ServerRequestInterface $request) : ServerRequestInterface
    {
        $body = (array) $request->getParsedBody();
        $body['department_id'] = $request->getAttribute('department_id');
        return $request->withParsedBody($body);
    }

    public function validateRequest(ServerRequestInterface $request) : void
    {
        ValidationService::validate($request, [
            'department_id' => new UuidValidator(),
        ]);
    }

    public function execute(ServerRequestInterface $request) : ResponseInterface
    {
        $body = (array) $request->getParsedBody();
        $departmentId = Uuid::fromString($body['department_id']);
        $department = $this->departmentRepository->getDepartment($departmentId);
        $departmentChildren = $this->getDepartmentChildren($department);
        $departmentUsers = $this->userRepository->getUsersForDepartment($department);
        $departmentMailboxes = $this->mailboxRepository->getMailboxesForDepartment($department);

        return new JsonResponse([
            'department' => [
                'department_id' => $department->getId()->toString(),
                'name' => $department->getName(),
                'parent_id' => $department->getParent() ? $department->getParent()->getId()->toString() : null,
                'email' => $department->getEmail()->toString(),
            ],
            'children' => array_map(function (Department $department) {
                return [
                    'department_id' => $department->getId()->toString(),
                    'name' => $department->getName(),
                    'parent_id' => $department->getParent() ? $department->getParent()->getId()->toString() : null,
                    'email' => $department->getEmail()->toString(),
                ];
            }, $departmentChildren->toArray()),
            'users' => array_map(function (User $user) {
                return [
                    'email' => $user->getEmail()->toString(),
                    'display_name' => $user->getDisplayName(),
                ];
            }, $departmentUsers->toArray()),
            'mailboxes' => array_map(function (Mailbox $mailbox) {
                return [
                    'mailbox_id' => $mailbox->getId()->toString(),
                    'name' => $mailbox->getName(),
                    'last_check' => $mailbox->getLastCheck()->format('Y-m-d H:i:s'),
                ];
            }, $departmentMailboxes->toArray()),
        ]);
    }

    public function getDepartmentChildren(?Department $parent) : DepartmentCollection
    {
        $children = new DepartmentCollection();
        $allDepartments = $this->departmentRepository->getDepartments();
        foreach ($allDepartments as $department) {
            if (!is_null($department->getParent()) && $department->getParent()->getId()->equals($parent->getId())) {
                $children->push($department);
            } elseif (is_null($department->getParent()) && is_null($parent)) {
                $children->push($department);
            }
        }
        return $children;
    }
}
