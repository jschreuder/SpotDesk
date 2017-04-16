<?php declare(strict_types=1);

namespace jschreuder\SpotDesk;

use jschreuder\Middle\Router\RouterInterface;
use jschreuder\Middle\Router\RoutingProviderInterface;
use jschreuder\Middle\Router\SymfonyRouter;
use jschreuder\SpotDesk\Controller\AddTicketUpdateController;
use jschreuder\SpotDesk\Controller\CreateDepartmentController;
use jschreuder\SpotDesk\Controller\CreateMailboxController;
use jschreuder\SpotDesk\Controller\GetOpenTicketsController;
use jschreuder\SpotDesk\Controller\GetTicketController;
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

    public function registerRoutes(RouterInterface $router): void
    {
        /** @var  SymfonyRouter  $router */

        // Tickets
        $router->get('tickets.get_one', '/tickets/{ticket_id}', function () {
            return new GetTicketController($this->container['repository.tickets']);
        })->setRequirement('ticket_id', Uuid::VALID_PATTERN);
        $router->get('tickets.list_open', '/tickets', function () {
            return new GetOpenTicketsController($this->container['repository.tickets']);
        });
        $router->post('tickets.add_update', '/tickets/{ticket_id}', function () {
            return new AddTicketUpdateController(
                $this->container['repository.tickets'],
                $this->container['repository.statuses']
            );
        })->setRequirement('ticket_id', Uuid::VALID_PATTERN);

        // Departments
        $router->post('departments.create', '/departments', function () {
            return new CreateDepartmentController($this->container['repository.departments']);
        });

        // Mailboxes
        $router->post('mailboxes.create', '/mailboxes', function () {
            return new CreateMailboxController(
                $this->container['repository.mailboxes'],
                $this->container['repository.departments']
            );
        });
    }
}
