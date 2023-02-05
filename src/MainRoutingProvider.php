<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk;

use jschreuder\Middle\Router\RouterInterface;
use jschreuder\Middle\Router\RoutingProviderInterface;
use jschreuder\Middle\Router\SymfonyRouter;
use jschreuder\SpotDesk\Controller\AngularInitController;
use jschreuder\SpotDesk\Controller\MailboxDeleteController;
use jschreuder\SpotDesk\Controller\MailboxUpdateController;
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
use Ramsey\Uuid\Validator\GenericValidator;

class MainRoutingProvider implements RoutingProviderInterface
{
    public function __construct(private ServiceContainer $container)
    {
    }

    public function registerRoutes(RouterInterface $router) : void
    {
        $uuidValidPattern = (new GenericValidator())->getPattern();

        /** @var  SymfonyRouter $router */

        $router->get('angular_init', '/', function () {
            return new AngularInitController($this->container->config('site.title'), $this->container->config('site.url'));
        });

        $router->put('change_password', '/change_password', function () {
            return new ChangePasswordController(
                $this->container->getUserRepository(),
                $this->container->getAuthenticationService()
            );
        });

        // Tickets
        $router->get('tickets.get_one', '/tickets/{ticket_id}', function () {
            return new TicketGetOneController($this->container->getTicketRepository());
        }, [], [], function ($route) use ($uuidValidPattern) {
            $route->setRequirement('ticket_id', $uuidValidPattern);
        });
        $router->get('tickets.list', '/tickets', function () {
            return new TicketGetAllController($this->container->getTicketRepository());
        });
        $router->post('tickets.add_update', '/tickets/{ticket_id}', function () {
            return new TicketAddUpdateController(
                $this->container->getTicketRepository(),
                $this->container->getStatusRepository(),
                $this->container->getSendMailService()
            );
        }, [], [], function ($route) use ($uuidValidPattern) {
            $route->setRequirement('ticket_id', $uuidValidPattern);
        });
        $router->put('tickets.status_update', '/tickets/{ticket_id}/status', function () {
            return new TicketUpdateStatusController(
                $this->container->getTicketRepository(),
                $this->container->getStatusRepository()
            );
        }, [], [], function ($route) use ($uuidValidPattern) {
            $route->setRequirement('ticket_id', $uuidValidPattern);
        });
        $router->put('tickets.department_update', '/tickets/{ticket_id}/department', function () {
            return new TicketUpdateDepartmentController(
                $this->container->getTicketRepository(),
                $this->container->getDepartmentRepository()
            );
        }, [], [], function ($route) use ($uuidValidPattern) {
            $route->setRequirement('ticket_id', $uuidValidPattern);
        });
        $router->delete('tickets.delete', '/tickets/{ticket_id}', function () {
            return new TicketDeleteController($this->container->getTicketRepository());
        }, [], [], function ($route) use ($uuidValidPattern) {
            $route->setRequirement('ticket_id', $uuidValidPattern);
        });

        // Departments
        $router->get('departments.list', '/departments', function () {
            return new DepartmentGetAllController($this->container->getDepartmentRepository());
        });
        $router->get('departments.get_one', '/departments/{department_id}', function () {
            return new DepartmentGetOneController(
                $this->container->getDepartmentRepository(),
                $this->container->getUserRepository(),
                $this->container->getMailboxRepository()
            );
        });
        $router->post('departments.create', '/departments', function () {
            return new DepartmentCreateController($this->container->getDepartmentRepository());
        });
        $router->put('departments.update', '/departments/{department_id}', function () {
            return new DepartmentUpdateController($this->container->getDepartmentRepository());
        }, [], [], function ($route) use ($uuidValidPattern) {
            $route->setRequirement('department_id', $uuidValidPattern);
        });
        $router->delete('departments.delete', '/departments/{department_id}', function () {
            return new DepartmentDeleteController(
                $this->container->getDepartmentRepository(),
                $this->container->getTicketRepository()
            );
        }, [], [], function ($route) use ($uuidValidPattern) {
            $route->setRequirement('department_id', $uuidValidPattern);
        });

        // Mailboxes
        $router->get('mailboxes.get_one', '/mailboxes/{mailbox_id}', function () {
            return new MailboxGetOneController($this->container->getMailboxRepository());
        }, [], [], function ($route) use ($uuidValidPattern) {
            $route->setRequirement('mailbox_id', $uuidValidPattern);
        });
        $router->get('mailboxes.list', '/mailboxes', function () {
            return new MailboxGetAllController($this->container->getMailboxRepository());
        });
        $router->post('mailboxes.create', '/mailboxes', function () {
            return new MailboxCreateController(
                $this->container->getMailboxRepository(),
                $this->container->getDepartmentRepository()
            );
        });
        $router->put('mailboxes.update', '/mailboxes/{mailbox_id}', function () {
            return new MailboxUpdateController(
                $this->container->getMailboxRepository(),
                $this->container->getDepartmentRepository()
            );
        }, [], [], function ($route) use ($uuidValidPattern) {
            $route->setRequirement('mailbox_id', $uuidValidPattern);
        });
        $router->delete('mailboxes.delete', '/mailboxes/{mailbox_id}', function () {
            return new MailboxDeleteController($this->container->getMailboxRepository());
        }, [], [], function ($route) use ($uuidValidPattern) {
            $route->setRequirement('mailbox_id', $uuidValidPattern);
        });

        // Users
        $router->get('users.list', '/users', function () {
            return new UserGetAllController($this->container->getUserRepository());
        });
        $router->get('users.get_one', '/users/{email}', function () {
            return new UserGetOneController(
                $this->container->getUserRepository(),
                $this->container->getDepartmentRepository()
            );
        });
        $router->post('users.create', '/users', function () {
            return new UserCreateController($this->container->getAuthenticationService(), $this->container->getRbac());
        });
        $router->put('users.update', '/users/{email}', function () {
            return new UserUpdateController($this->container->getUserRepository(), $this->container->getRbac());
        });
        $router->put('users.departments', '/users/{email}/departments', function () {
            return new UserUpdateDepartmentsController(
                $this->container->getUserRepository(),
                $this->container->getDepartmentRepository()
            );
        });
        $router->delete('users.delete', '/users/{email}', function () {
            return new UserDeleteController($this->container->getUserRepository());
        });

        // Statuses
        $router->get('statuses.list', '/statuses', function () {
            return new StatusesGetAllController($this->container->getStatusRepository());
        });
    }
}
