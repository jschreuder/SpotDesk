<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Controller;

use jschreuder\Middle\Controller\ControllerInterface;
use jschreuder\Middle\Controller\RequestFilterInterface;
use jschreuder\Middle\Controller\RequestValidatorInterface;
use jschreuder\SpotDesk\Entity\Department;
use jschreuder\SpotDesk\Exception\SpotDeskException;
use jschreuder\SpotDesk\Repository\DepartmentRepository;
use jschreuder\SpotDesk\Service\FilterService;
use jschreuder\SpotDesk\Service\ValidationService;
use jschreuder\SpotDesk\Value\EmailAddressValue;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Filter\FilterChain;
use Laminas\Filter\StringTrim;
use Laminas\Filter\StripTags;
use Laminas\Validator\EmailAddress as EmailAddressValidator;
use Laminas\Validator\StringLength;
use Laminas\Validator\Uuid as UuidValidator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;

class DepartmentUpdateController implements ControllerInterface, RequestFilterInterface, RequestValidatorInterface
{
    public function __construct(private DepartmentRepository $departmentRepository)
    {
    }

    public function filterRequest(ServerRequestInterface $request) : ServerRequestInterface
    {
        $body = (array) $request->getParsedBody();
        $body['department_id'] = $request->getAttribute('department_id');
        return FilterService::filter($request->withParsedBody($body), [
            'name' => (new FilterChain())->attach(new StripTags())->attach(new StringTrim()), 
            'email' => new StringTrim(),
        ]);
    }

    public function validateRequest(ServerRequestInterface $request) : void
    {
        ValidationService::validate($request, [
            'department_id' => new UuidValidator(),
            'name' => new StringLength(['min' => 1]),
            'parent_id' => new UuidValidator(),
            'email' => new EmailAddressValidator(),
        ], ['parent_id']);
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
