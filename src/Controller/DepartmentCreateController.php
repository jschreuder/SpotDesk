<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Controller;

use jschreuder\Middle\Controller\ControllerInterface;
use jschreuder\Middle\Controller\RequestFilterInterface;
use jschreuder\Middle\Controller\RequestValidatorInterface;
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

class DepartmentCreateController implements ControllerInterface, RequestValidatorInterface, RequestFilterInterface
{
    public function __construct(private DepartmentRepository $departmentRepository)
    {
    }

    public function filterRequest(ServerRequestInterface $request) : ServerRequestInterface
    {
        return FilterService::filter($request, [
            'name' => (new FilterChain())->attach(new StripTags())->attach(new StringTrim()), 
            'email' => new StringTrim(),
        ]);
    }

    public function validateRequest(ServerRequestInterface $request) : void
    {
        ValidationService::validate($request, [
            'name' => new StringLength(['min' => 1]),
            'parent_id' => new UuidValidator(),
            'email' => new EmailAddressValidator(),
        ], ['parent_id']);
    }

    public function execute(ServerRequestInterface $request) : ResponseInterface
    {
        $body = (array) $request->getParsedBody();

        $department = $this->departmentRepository->createDepartment(
            $body['name'],
            empty($body['parent_id'])
                ? null
                : $this->departmentRepository->getDepartment(Uuid::fromString($body['parent_id'])),
            EmailAddressValue::get($body['email'])
        );

        return new JsonResponse(['department_id' => $department->getId()->toString()], 201);
    }
}
