<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk;

use jschreuder\Middle\Controller\CallableController;
use jschreuder\Middle\Router\RouterInterface;
use jschreuder\Middle\Router\RoutingProviderInterface;
use jschreuder\Middle\Router\SymfonyRouter;
use jschreuder\SpotDesk\Controller\TicketAddUpdateController;
use jschreuder\SpotDesk\Controller\ChangePasswordController;
use jschreuder\SpotDesk\Controller\DepartmentDeleteController;
use jschreuder\SpotDesk\Controller\TicketDeleteController;
use jschreuder\SpotDesk\Controller\UserDeleteController;
use jschreuder\SpotDesk\Controller\DepartmentCreateController;
use jschreuder\SpotDesk\Controller\MailboxCreateController;
use jschreuder\SpotDesk\Controller\UserCreateController;
use jschreuder\SpotDesk\Controller\DepartmentGetOneController;
use jschreuder\SpotDesk\Controller\DepartmentGetAllController;
use jschreuder\SpotDesk\Controller\MailboxGetOneController;
use jschreuder\SpotDesk\Controller\MailboxGetAllController;
use jschreuder\SpotDesk\Controller\StatusesGetAllController;
use jschreuder\SpotDesk\Controller\TicketGetAllController;
use jschreuder\SpotDesk\Controller\TicketGetOneController;
use jschreuder\SpotDesk\Controller\UserGetOneController;
use jschreuder\SpotDesk\Controller\UserGetAllController;
use jschreuder\SpotDesk\Controller\DepartmentUpdateController;
use jschreuder\SpotDesk\Controller\TicketUpdateDepartmentController;
use jschreuder\SpotDesk\Controller\TicketUpdateStatusController;
use jschreuder\SpotDesk\Controller\UserUpdateController;
use jschreuder\SpotDesk\Controller\UserUpdateDepartmentsController;
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
            return new TicketGetOneController($this->container['repository.tickets']);
        })->setRequirement('ticket_id', Uuid::VALID_PATTERN);
        $router->get('tickets.list', '/tickets', function () {
            return new TicketGetAllController($this->container['repository.tickets']);
        });
        $router->post('tickets.add_update', '/tickets/{ticket_id}', function () {
            return new TicketAddUpdateController(
                $this->container['repository.tickets'],
                $this->container['repository.statuses'],
                $this->container['service.mail']
            );
        })->setRequirement('ticket_id', Uuid::VALID_PATTERN);
        $router->put('tickets.status_update', '/tickets/{ticket_id}/status', function () {
            return new TicketUpdateStatusController(
                $this->container['repository.tickets'],
                $this->container['repository.statuses']
            );
        })->setRequirement('ticket_id', Uuid::VALID_PATTERN);
        $router->put('tickets.department_update', '/tickets/{ticket_id}/department', function () {
            return new TicketUpdateDepartmentController(
                $this->container['repository.tickets'],
                $this->container['repository.departments']
            );
        })->setRequirement('ticket_id', Uuid::VALID_PATTERN);
        $router->delete('tickets.delete', '/tickets/{ticket_id}', function () {
            return new TicketDeleteController($this->container['repository.tickets']);
        })->setRequirement('ticket_id', Uuid::VALID_PATTERN);

        // Departments
        $router->get('departments.list', '/departments', function () {
            return new DepartmentGetAllController($this->container['repository.departments']);
        });
        $router->get('departments.get_one', '/departments/{department_id}', function () {
            return new DepartmentGetOneController(
                $this->container['repository.departments'],
                $this->container['repository.users'],
                $this->container['repository.mailboxes']
            );
        });
        $router->post('departments.create', '/departments', function () {
            return new DepartmentCreateController($this->container['repository.departments']);
        });
        $router->put('departments.update', '/departments/{department_id}', function () {
            return new DepartmentUpdateController($this->container['repository.departments']);
        })->setRequirement('department_id', Uuid::VALID_PATTERN);
        $router->delete('departments.delete', '/departments/{department_id}', function () {
            return new DepartmentDeleteController(
                $this->container['repository.departments'],
                $this->container['repository.tickets']
            );
        })->setRequirement('department_id', Uuid::VALID_PATTERN);

        // Mailboxes
        $router->get('mailboxes.get_one', '/mailboxes/{mailbox_id}', function () {
            return new MailboxGetOneController($this->container['repository.mailboxes']);
        })->setRequirement('mailbox_id', Uuid::VALID_PATTERN);
        $router->get('mailboxes.list', '/mailboxes', function () {
            return new MailboxGetAllController($this->container['repository.mailboxes']);
        });
        $router->post('mailboxes.create', '/mailboxes', function () {
            return new MailboxCreateController(
                $this->container['repository.mailboxes'],
                $this->container['repository.departments']
            );
        });

        // Users
        $router->get('users.list', '/users', function () {
            return new UserGetAllController($this->container['repository.users']);
        });
        $router->get('users.get_one', '/users/{email}', function () {
            return new UserGetOneController(
                $this->container['repository.users'],
                $this->container['repository.departments']
            );
        });
        $router->post('users.create', '/users', function () {
            return new UserCreateController($this->container['service.authentication']);
        });
        $router->put('users.update', '/users/{email}', function () {
            return new UserUpdateController($this->container['repository.users']);
        });
        $router->put('users.departments', '/users/{email}/departments', function () {
            return new UserUpdateDepartmentsController(
                $this->container['repository.users'],
                $this->container['repository.departments']
            );
        });
        $router->delete('users.delete', '/users/{email}', function () {
            return new UserDeleteController($this->container['repository.users']);
        });

        // Statuses
        $router->get('statuses.list', '/statuses', function () {
            return new StatusesGetAllController($this->container['repository.statuses']);
        });
    }
}
