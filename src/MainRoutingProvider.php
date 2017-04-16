<?php declare(strict_types=1);

namespace jschreuder\SpotDesk;

use jschreuder\Middle\Router\RouterInterface;
use jschreuder\Middle\Router\RoutingProviderInterface;
use jschreuder\SpotDesk\Controller\GetOpenTicketsController;
use Pimple\Container;

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
        $router->get('tickets.open', '/tickets', function () {
            return new GetOpenTicketsController($this->container['repository.tickets']);
        });
    }
}