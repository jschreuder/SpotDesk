<?php declare(strict_types=1);

namespace jschreuder\SpotDesk\Controller;

use jschreuder\Middle\Controller\ControllerInterface;
use jschreuder\Middle\Controller\RequestFilterInterface;
use jschreuder\Middle\Controller\RequestValidatorInterface;
use jschreuder\Middle\Controller\ValidationFailedException;
use jschreuder\SpotDesk\Repository\DepartmentRepository;
use Particle\Filter\Filter;
use Particle\Validator\Validator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;
use Zend\Diactoros\Response\JsonResponse;

class CreateDepartmentController implements ControllerInterface, RequestValidatorInterface, RequestFilterInterface
{
    /** @var  DepartmentRepository */
    private $departmentRepository;

    public function __construct(DepartmentRepository $departmentRepository)
    {
        $this->departmentRepository = $departmentRepository;
    }

    public function filterRequest(ServerRequestInterface $request): ServerRequestInterface
    {
        $body = $request->getParsedBody();
        $filter = new Filter();
        $filter->value('name')->string()->stripHtml()->trim();
        $filter->value('parent_id')->string()->trim();

        return $request->withParsedBody($filter->filter($body));
    }

    public function validateRequest(ServerRequestInterface $request): void
    {
        $validator = new Validator();
        $validator->required('name')->string();
        $validator->optional('parent_id')->uuid();

        $validationResult = $validator->validate($request->getParsedBody());
        if (!$validationResult->isValid()) {
            throw new ValidationFailedException($validationResult->getMessages());
        }
    }

    public function execute(ServerRequestInterface $request): ResponseInterface
    {
        $body = $request->getParsedBody();

        $department = $this->departmentRepository->createDepartment(
            $body['name'],
            empty($body['parent_id'])
                ? null
                : $this->departmentRepository->getDepartment(Uuid::fromString($body['parent_id']))
        );

        return new JsonResponse(['department_id' => $department->getId()->toString()], 201);
    }
}
