<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Controller;

use jschreuder\Middle\Controller\ControllerInterface;
use jschreuder\Middle\Controller\RequestFilterInterface;
use jschreuder\Middle\Controller\RequestValidatorInterface;
use jschreuder\Middle\Controller\ValidationFailedException;
use jschreuder\SpotDesk\Collection\DepartmentCollection;
use jschreuder\SpotDesk\Entity\Department;
use jschreuder\SpotDesk\Entity\Mailbox;
use jschreuder\SpotDesk\Entity\User;
use jschreuder\SpotDesk\Repository\DepartmentRepository;
use jschreuder\SpotDesk\Repository\MailboxRepository;
use jschreuder\SpotDesk\Repository\UserRepository;
use Particle\Filter\Filter;
use Particle\Validator\Validator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;
use Zend\Diactoros\Response\JsonResponse;

class DepartmentGetOneController implements ControllerInterface, RequestFilterInterface, RequestValidatorInterface
{
    /** @var  DepartmentRepository */
    private $departmentRepository;

    /** @var  UserRepository */
    private $userRepository;

    /** @var  MailboxRepository */
    private $mailboxRepository;

    public function __construct(
        DepartmentRepository $departmentRepository,
        UserRepository $userRepository,
        MailboxRepository $mailboxRepository
    ) {
        $this->departmentRepository = $departmentRepository;
        $this->userRepository = $userRepository;
        $this->mailboxRepository = $mailboxRepository;
    }

    public function filterRequest(ServerRequestInterface $request) : ServerRequestInterface
    {
        $body = (array) $request->getParsedBody();
        $body['department_id'] = $request->getAttribute('department_id');
        $filter = new Filter();
        $filter->value('department_id')->string()->trim();

        return $request->withParsedBody($filter->filter($body));
    }

    public function validateRequest(ServerRequestInterface $request) : void
    {
        $validator = new Validator();
        $validator->required('department_id')->uuid();

        $validationResult = $validator->validate((array) $request->getParsedBody());
        if (!$validationResult->isValid()) {
            throw new ValidationFailedException($validationResult->getMessages());
        }
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
                'parent_name' => $department->getParent() ? $department->getParent()->getName() : null,
                'email' => $department->getEmail()->toString(),
            ],
            'children' => array_map(function (Department $department) {
                return [
                    'department_id' => $department->getId()->toString(),
                    'name' => $department->getName(),
                    'parent_id' => $department->getParent() ? $department->getParent()->getId()->toString() : null,
                    'parent_name' => $department->getParent() ? $department->getParent()->getName() : null,
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
