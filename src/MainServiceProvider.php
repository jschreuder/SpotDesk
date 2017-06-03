<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk;

use jschreuder\Middle\ApplicationStack;
use jschreuder\Middle\Controller\ControllerRunner;
use jschreuder\Middle\Exception\ValidationFailedException;
use jschreuder\Middle\Router\RouterInterface;
use jschreuder\Middle\Router\SymfonyRouter;
use jschreuder\Middle\ServerMiddleware\ErrorHandlerMiddleware;
use jschreuder\Middle\ServerMiddleware\JsonRequestParserMiddleware;
use jschreuder\Middle\ServerMiddleware\RequestFilterMiddleware;
use jschreuder\Middle\ServerMiddleware\RequestValidatorMiddleware;
use jschreuder\Middle\ServerMiddleware\RoutingMiddleware;
use jschreuder\Middle\ServerMiddleware\SessionMiddleware;
use jschreuder\SpotDesk\Controller\ErrorHandlerController;
use jschreuder\SpotDesk\Controller\NotFoundHandlerController;
use jschreuder\SpotDesk\Middleware\AuthenticationMiddleware;
use jschreuder\SpotDesk\Middleware\AuthorizationMiddleware;
use jschreuder\SpotDesk\Middleware\SecurityHeadersMiddleware;
use jschreuder\SpotDesk\Repository\DepartmentRepository;
use jschreuder\SpotDesk\Repository\MailboxRepository;
use jschreuder\SpotDesk\Repository\StatusRepository;
use jschreuder\SpotDesk\Repository\TicketMailingRepository;
use jschreuder\SpotDesk\Repository\TicketRepository;
use jschreuder\SpotDesk\Repository\UserRepository;
use jschreuder\SpotDesk\Service\AuthenticationService\AuthenticationService;
use jschreuder\SpotDesk\Service\AuthorizationService\AuthorizableControllerInterface;
use jschreuder\SpotDesk\Service\SessionService\JwtSessionStorage;
use jschreuder\SpotDesk\Service\SessionService\SessionProcessor;
use jschreuder\SpotDesk\Service\SendMailService\MailTemplateFactory;
use jschreuder\SpotDesk\Service\SendMailService\SmtpSendMailService;
use jschreuder\SpotDesk\Service\SendMailService\TwigMailTemplate;
use jschreuder\SpotDesk\Value\EmailAddressValue;
use Lcobucci\JWT\Signer\Hmac\Sha512;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Permissions\Rbac\Rbac;
use Zend\Permissions\Rbac\Role;

class MainServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container) : void
    {
        $container['app'] = function (Container $container) {
            return new ApplicationStack(
                new ControllerRunner(),
                new RequestValidatorMiddleware($container['requestValidator.errorHandler']),
                new RequestFilterMiddleware(),
                new AuthorizationMiddleware($container['rbac']),
                new AuthenticationMiddleware($container['service.authentication'], $container['repository.users']),
                new SessionMiddleware($container['session.processor']),
                new JsonRequestParserMiddleware(),
                new RoutingMiddleware(
                    $container['app.router'],
                    $container['app.error_handlers.404']
                ),
                new SecurityHeadersMiddleware($container['site.url']),
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

        $container['app.error_handlers.404'] = function () use ($container) {
            return new ErrorHandlerController($container['logger']);
        };

        $container['app.error_handlers.500'] = function () {
            return new NotFoundHandlerController();
        };

        $container['requestValidator.errorHandler'] = $container->protect(function (
            ServerRequestInterface $request,
            ValidationFailedException $validationFailedException
        ) : ResponseInterface {
            return new JsonResponse([
                'validation_errors' => array_map(function (array $errors) {
                        return array_keys($errors);
                    }, $validationFailedException->getValidationErrors()),
            ], 400);
        });

        $container['db'] = function () use ($container) {
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

        $container['rbac'] = function () {
            $rbac = new Rbac();
            $rbac->addRole((new Role('admin'))
                ->addPermission(AuthorizableControllerInterface::ROLE_ADMIN));
            $rbac->addRole((new Role('guest'))
                ->addPermission(AuthorizableControllerInterface::ROLE_PUBLIC));
            return $rbac;
        };

        $container['mail.swiftmailer'] = function () use ($container) {
            $transport = \Swift_SmtpTransport::newInstance(
                $container['smtp.server'],
                $container['smtp.port'],
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
                new TwigMailTemplate($container['mail.twig'], 'new_ticket.twig'),
                new TwigMailTemplate($container['mail.twig'], 'update_ticket.twig')
            );
        };

        $container['session.storage'] = function () use ($container) {
            return new JwtSessionStorage(
                $container['site.url'],
                new Sha512(),
                $container['session.secret_key']
            );
        };

        $container['session.processor'] = function () use ($container) {
            return new SessionProcessor(
                $container['session.storage'],
                $container['session.duration'],
                $container['session.refresh_after']
            );
        };

        $container['service.authentication'] = function () use ($container) {
            return new AuthenticationService(
                $container['repository.users'],
                $container['rbac'],
                $container['password.algo'],
                $container['password.options']
            );
        };

        $container['service.mail'] = function () use ($container) {
            return new SmtpSendMailService(
                $container['repository.ticket_mailings'],
                $container['mail.swiftmailer'],
                $container['mail.template_factory'],
                EmailAddressValue::get($container['smtp.from']),
                $container['site.title']
            );
        };

        $container['repository.users'] = function () use ($container) {
            return new UserRepository($container['db'], $container['rbac']);
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
