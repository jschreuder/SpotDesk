<?php declare(strict_types=1);

namespace jschreuder\SpotDesk;

use jschreuder\Middle\ApplicationStack;
use jschreuder\Middle\Controller\CallableController;
use jschreuder\Middle\Controller\ControllerRunner;
use jschreuder\Middle\Controller\ValidationFailedException;
use jschreuder\Middle\Router\RouterInterface;
use jschreuder\Middle\Router\SymfonyRouter;
use jschreuder\Middle\ServerMiddleware\ErrorHandlerMiddleware;
use jschreuder\Middle\ServerMiddleware\JsonRequestParserMiddleware;
use jschreuder\Middle\ServerMiddleware\RequestFilterMiddleware;
use jschreuder\Middle\ServerMiddleware\RequestValidatorMiddleware;
use jschreuder\Middle\ServerMiddleware\RoutingMiddleware;
use jschreuder\SpotDesk\Middleware\AuthenticationMiddleware;
use jschreuder\SpotDesk\Repository\DepartmentRepository;
use jschreuder\SpotDesk\Repository\MailboxRepository;
use jschreuder\SpotDesk\Repository\StatusRepository;
use jschreuder\SpotDesk\Repository\TicketMailingRepository;
use jschreuder\SpotDesk\Repository\TicketRepository;
use jschreuder\SpotDesk\Repository\UserRepository;
use jschreuder\SpotDesk\Service\AuthenticationService\JwtAuthenticationService;
use jschreuder\SpotDesk\Service\SendMailService\MailTemplateFactory;
use jschreuder\SpotDesk\Service\SendMailService\SmtpSendMailService;
use jschreuder\SpotDesk\Service\SendMailService\TwigMailTemplate;
use Lcobucci\JWT\Signer\Hmac\Sha512;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;

class MainServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container): void
    {
        $container['app'] = function (Container $container) {
            return new ApplicationStack(
                new ControllerRunner(),
                new RequestValidatorMiddleware($container['requestValidator.errorHandler']),
                new RequestFilterMiddleware(),
                new AuthenticationMiddleware($container['service.authentication']),
                new JsonRequestParserMiddleware(),
                new RoutingMiddleware(
                    $container['app.router'],
                    $container['app.error_handlers.404']
                ),
                new ErrorHandlerMiddleware(
                    $container['logger'],
                    $container['app.error_handlers.500']
                )
            );
        };

        $container['app.router'] = function () use ($container) {
            return new SymfonyRouter($container['site.url']);
        };

        $container['app.url_generator'] = function () use ($container) {
            /** @var  RouterInterface $router */
            $router = $container['app.router'];
            return $router->getGenerator();
        };

        $container['app.error_handlers.404'] = CallableController::factoryFromCallable(
            function (ServerRequestInterface $request) use ($container) : ResponseInterface {
                return new JsonResponse(
                    [
                        'message' => 'Not found: ' .
                            $request->getMethod() . ' ' . $request->getUri()->getPath(),
                    ],
                    404
                );
            }
        );

        $container['app.error_handlers.500'] = CallableController::factoryFromCallable(
            function () use ($container) : ResponseInterface {
                return new JsonResponse(['message' => 'System Error'], 500);
            }
        );

        $container['requestValidator.errorHandler'] = $container->protect(function (
            ServerRequestInterface $request,
            ValidationFailedException $validationFailedException
        ): ResponseInterface {
            return new JsonResponse([
                'errors' => $validationFailedException->getValidationErrors(),
            ], 400);
        });

        $container['db'] = function (Container $container) {
            return new \PDO(
                $container['db.dsn'] . ';dbname=' . $container['db.dbname'],
                $container['db.user'],
                $container['db.pass'],
                [
                    \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                ]
            );
        };

        $container['mail.swiftmailer'] = function () use ($container) {
            $transport = \Swift_SmtpTransport::newInstance(
                    $container['smtp.server'],
                    $container['smtp.host'],
                    $container['smtp.security']
                )
                ->setUsername($container['smtp.user'])
                ->setPassword($container['smtp.pass']);
            return \Swift_Mailer::newInstance($transport);
        };

        $container['mail.twig'] = function () use ($container) {
            return new \Twig_Environment(new \Twig_Loader_Filesystem([__DIR__ . '/../templates/emails']));
        };

        $container['mail.template_factory'] = function () use ($container) {
            return new MailTemplateFactory(
                new TwigMailTemplate($container['mail.twig'], 'new_ticket.twig', 'New ticket created'),
                new TwigMailTemplate($container['mail.twig'], 'update_ticket.twig', 'Ticket updated')
            );
        };

        $container['service.authentication'] = function () use ($container) {
            return new JwtAuthenticationService(
                $container['repository.users'],
                $container['password.algo'],
                $container['password.options'],
                $container['site.url'],
                new Sha512(),
                $container['session.secret_key'],
                $container['session.duration'],
                $container['session.refresh_after']
            );
        };

        $container['service.mail'] = function () use ($container) {
            return new SmtpSendMailService(
                $container['repository.ticket_mailings'],
                $container['mail.swiftmailer'],
                $container['mail.template_factory']
            );
        };

        $container['repository.users'] = function () use ($container) {
            return new UserRepository($container['db']);
        };

        $container['repository.tickets'] = function () use ($container) {
            return new TicketRepository(
                $container['db'],
                $container['repository.statuses'],
                $container['repository.departments']
            );
        };

        $container['repository.statuses'] = function () use ($container) {
            return new StatusRepository($container['db']);
        };

        $container['repository.departments'] = function () use ($container) {
            return new DepartmentRepository($container['db']);
        };

        $container['repository.mailboxes'] = function () use ($container) {
            return new MailboxRepository($container['db'], $container['repository.departments']);
        };

        $container['repository.ticket_mailings'] = function () use ($container) {
            return new TicketMailingRepository($container['db'], $container['repository.tickets']);
        };
    }
}
