<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk;

use jschreuder\Middle\Controller\CallableController;
use jschreuder\Middle\Router\RouterInterface;
use jschreuder\Middle\Router\RoutingProviderInterface;
use jschreuder\Middle\Router\SymfonyRouter;
use jschreuder\SpotDesk\Controller\AddTicketUpdateController;
use jschreuder\SpotDesk\Controller\ChangePasswordController;
use jschreuder\SpotDesk\Controller\DeleteTicketController;
use jschreuder\SpotDesk\Controller\DeleteUserController;
use jschreuder\SpotDesk\Controller\CreateDepartmentController;
use jschreuder\SpotDesk\Controller\CreateMailboxController;
use jschreuder\SpotDesk\Controller\CreateUserController;
use jschreuder\SpotDesk\Controller\GetDepartmentsController;
use jschreuder\SpotDesk\Controller\GetMailboxesController;
use jschreuder\SpotDesk\Controller\GetStatusesController;
use jschreuder\SpotDesk\Controller\GetTicketsController;
use jschreuder\SpotDesk\Controller\GetTicketController;
use jschreuder\SpotDesk\Controller\GetUserController;
use jschreuder\SpotDesk\Controller\GetUsersController;
use jschreuder\SpotDesk\Controller\UpdateDepartmentController;
use jschreuder\SpotDesk\Controller\UpdateTicketDepartmentController;
use jschreuder\SpotDesk\Controller\UpdateTicketStatusController;
use jschreuder\SpotDesk\Controller\UpdateUserDepartmentsController;
use Pimple\Container;
use Ramsey\Uuid\Uuid;

class MainRoutingProvider implements RoutingProviderInterface
{
    /** @var  Container */
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function registerRoutes(RouterInterface $router) : void
    {
        /** @var  SymfonyRouter $router */

        $router->get('homepage', '/', function () {
            return CallableController::fromCallable($this->container['site.template']);
        });
        $router->put('change_password', '/change_password', function () {
            return new ChangePasswordController(
                $this->container['repository.users'],
                $this->container['service.authentication']
            );
        });

        // Tickets
        $router->get('tickets.get_one', '/tickets/{ticket_id}', function () {
            return new GetTicketController($this->container['repository.tickets']);
        })->setRequirement('ticket_id', Uuid::VALID_PATTERN);
        $router->get('tickets.list', '/tickets', function () {
            return new GetTicketsController($this->container['repository.tickets']);
        });
        $router->post('tickets.add_update', '/tickets/{ticket_id}', function () {
            return new AddTicketUpdateController(
                $this->container['repository.tickets'],
                $this->container['repository.statuses'],
                $this->container['service.mail']
            );
        })->setRequirement('ticket_id', Uuid::VALID_PATTERN);
        $router->put('tickets.status_update', '/tickets/{ticket_id}/status', function () {
            return new UpdateTicketStatusController(
                $this->container['repository.tickets'],
                $this->container['repository.statuses']
            );
        })->setRequirement('ticket_id', Uuid::VALID_PATTERN);
        $router->put('tickets.department_update', '/tickets/{ticket_id}/department', function () {
            return new UpdateTicketDepartmentController(
                $this->container['repository.tickets'],
                $this->container['repository.departments']
            );
        })->setRequirement('ticket_id', Uuid::VALID_PATTERN);
        $router->delete('tickets.delete', '/tickets/{ticket_id}', function () {
            return new DeleteTicketController($this->container['repository.tickets']);
        })->setRequirement('ticket_id', Uuid::VALID_PATTERN);

        // Departments
        $router->get('departments.list', '/departments', function () {
            return new GetDepartmentsController($this->container['repository.departments']);
        });
        $router->post('departments.create', '/departments', function () {
            return new CreateDepartmentController($this->container['repository.departments']);
        });
        $router->put('departments.update', '/departments/{department_id}', function () {
            return new UpdateDepartmentController($this->container['repository.departments']);
        })->setRequirement('department_id', Uuid::VALID_PATTERN);

        // Mailboxes
        $router->get('mailboxes.list', '/mailboxes', function () {
            return new GetMailboxesController($this->container['repository.mailboxes']);
        });
        $router->post('mailboxes.create', '/mailboxes', function () {
            return new CreateMailboxController(
                $this->container['repository.mailboxes'],
                $this->container['repository.departments']
            );
        });

        // Users
        $router->get('users.list', '/users', function () {
            return new GetUsersController($this->container['repository.users']);
        });
        $router->get('users.get_one', '/users/{email}', function () {
            return new GetUserController(
                $this->container['repository.users'],
                $this->container['repository.departments']
            );
        });
        $router->post('users.create', '/users', function () {
            return new CreateUserController($this->container['service.authentication']);
        });
        $router->put('users.departments', '/users/{email}/departments', function () {
            return new UpdateUserDepartmentsController(
                $this->container['repository.users'],
                $this->container['repository.departments']
            );
        });
        $router->delete('users.delete', '/users/{email}', function () {
            return new DeleteUserController($this->container['repository.users']);
        });

        // Statuses
        $router->get('statuses.list', '/statuses', function () {
            return new GetStatusesController($this->container['repository.statuses']);
        });
    }
}
