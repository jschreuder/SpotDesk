<?php declare(strict_types = 1);

use jschreuder\SpotDesk\ServiceContainer;

/** @var  ServiceContainer $container */
$container = require __DIR__ . '/../config/app_init.php';

/** @var  jschreuder\Middle\ApplicationStackInterface $app */
$app = $container->getApp();

// Register routing
(new \jschreuder\Middle\Router\RoutingProviderCollection(
    new jschreuder\SpotDesk\MainRoutingProvider($container)
))->registerRoutes($container->getRouter());

// Create request from globals
$request = \Laminas\Diactoros\ServerRequestFactory::fromGlobals();

// Execute the application
$response = $app->process($request);

// Output the response
(new Laminas\HttpHandlerRunner\Emitter\SapiEmitter())->emit($response);
