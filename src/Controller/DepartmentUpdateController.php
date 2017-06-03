<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Controller;

use jschreuder\Middle\Controller\ControllerInterface;
use jschreuder\Middle\Controller\RequestFilterInterface;
use jschreuder\Middle\Controller\RequestValidatorInterface;
use jschreuder\Middle\Exception\ValidationFailedException;
use jschreuder\SpotDesk\Entity\Department;
use jschreuder\SpotDesk\Exception\SpotDeskException;
use jschreuder\SpotDesk\Repository\DepartmentRepository;
use jschreuder\SpotDesk\Value\EmailAddressValue;
use Particle\Filter\Filter;
use Particle\Validator\Validator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;
use Zend\Diactoros\Response\JsonResponse;

class DepartmentUpdateController implements ControllerInterface, RequestFilterInterface, RequestValidatorInterface
{
    /** @var  DepartmentRepository */
    private $departmentRepository;

    public function __construct(DepartmentRepository $departmentRepository)
    {
        $this->departmentRepository = $departmentRepository;
    }

    public function filterRequest(ServerRequestInterface $request) : ServerRequestInterface
    {
        $body = (array) $request->getParsedBody();
        $body['department_id'] = $request->getAttribute('department_id');
        $filter = new Filter();
        $filter->value('name')->string()->stripHtml()->trim();
        $filter->value('email')->string()->trim();

        return $request->withParsedBody($filter->filter($body));
    }

    public function validateRequest(ServerRequestInterface $request) : void
    {
        $validator = new Validator();
        $validator->required('department_id')->uuid();
        $validator->required('name')->string();
        $validator->required('email')->email();
        $validator->optional('parent_id')->uuid();

        $validationResult = $validator->validate((array) $request->getParsedBody());
        if (!$validationResult->isValid()) {
            throw new ValidationFailedException($validationResult->getMessages());
        }
    }

    public function execute(ServerRequestInterface $request) : ResponseInterface
    {
        $body = (array) $request->getParsedBody();
        $department = $this->departmentRepository->getDepartment(Uuid::fromString($body['department_id']));

        $parent = $body['parent_id']
            ? $this->departmentRepository->getDepartment(Uuid::fromString($body['parent_id']))
            : null;
        $this->checkParentage($department, $parent);

        $department->setName($body['name']);
        $department->setEmail(EmailAddressValue::get($body['email']));
        $department->setParent($parent);
        $this->departmentRepository->updateDepartment($department);

        return new JsonResponse([
            'department' => [
                'department_id' => $department->getId()->toString(),
                'name' => $department->getName(),
                'parent_id' => $department->getParent() ? $department->getParent()->getId()->toString() : null,
                'email' => $department->getEmail()->toString(),
            ],
        ], 200);
    }

    private function checkParentage(Department $department, ?Department $parent)
    {
        while ($parent) {
            if ($parent->getId()->equals($department->getId())) {
                throw new SpotDeskException('Child cannot be a parent of its own parent.');
            }
            $parent = $parent->getParent();
        }
    }
}
