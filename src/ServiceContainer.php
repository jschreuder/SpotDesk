<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk;

use Closure;
use jschreuder\Middle\ApplicationStack;
use jschreuder\Middle\Controller\ControllerInterface;
use jschreuder\Middle\Controller\ControllerRunner;
use jschreuder\Middle\Exception\ValidationFailedException;
use jschreuder\Middle\Router\RouterInterface;
use jschreuder\Middle\Router\SymfonyRouter;
use jschreuder\Middle\Router\UrlGeneratorInterface;
use jschreuder\Middle\ServerMiddleware\ErrorHandlerMiddleware;
use jschreuder\Middle\ServerMiddleware\JsonRequestParserMiddleware;
use jschreuder\Middle\ServerMiddleware\RequestFilterMiddleware;
use jschreuder\Middle\ServerMiddleware\RequestValidatorMiddleware;
use jschreuder\Middle\ServerMiddleware\RoutingMiddleware;
use jschreuder\Middle\ServerMiddleware\SessionMiddleware;
use jschreuder\Middle\Session\SessionProcessorInterface;
use jschreuder\MiddleDi\ConfigTrait;
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
use jschreuder\SpotDesk\Service\AuthenticationService\AuthenticationServiceInterface;
use jschreuder\SpotDesk\Service\AuthorizationService\AuthorizableControllerInterface;
use jschreuder\SpotDesk\Service\FetchMailService\FetchMailServiceInterface;
use jschreuder\SpotDesk\Service\FetchMailService\PhpImapFetchMailService;
use jschreuder\SpotDesk\Service\SessionService\JwtSessionStorage;
use jschreuder\SpotDesk\Service\SessionService\SessionProcessor;
use jschreuder\SpotDesk\Service\SendMailService\MailTemplateFactory;
use jschreuder\SpotDesk\Service\SendMailService\SendMailServiceInterface;
use jschreuder\SpotDesk\Service\SendMailService\SmtpSendMailService;
use jschreuder\SpotDesk\Service\SendMailService\TwigMailTemplate;
use jschreuder\SpotDesk\Service\SessionService\SessionStorageInterface;
use jschreuder\SpotDesk\Value\EmailAddressValue;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Permissions\Rbac\Rbac;
use Laminas\Permissions\Rbac\Role;
use Lcobucci\JWT\Configuration as JwtConfiguration;
use Lcobucci\JWT\Signer\Hmac\Sha512;
use Lcobucci\JWT\Signer\Key\InMemory;
use PDO;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class ServiceContainer
{
    use ConfigTrait;

    public function getApp(): ApplicationStack
    {
        return new ApplicationStack(
            new ControllerRunner(),
            new RequestValidatorMiddleware($this->newRequestValidatorErrorHandler()),
            new RequestFilterMiddleware(),
            new AuthorizationMiddleware($this->getRbac()),
            new AuthenticationMiddleware($this->getAuthenticationService()),
            new SessionMiddleware($this->getSessionProcessor()),
            new JsonRequestParserMiddleware(),
            new RoutingMiddleware(
                $this->getRouter(),
                $this->get404Handler()
            ),
            new SecurityHeadersMiddleware($this->config('site.url')),
            new ErrorHandlerMiddleware(
                $this->getLogger(),
                $this->get500Handler()
            )
        );
    }

    public function getLogger(): LoggerInterface
    {
        $logger = new \Monolog\Logger($this->config('logger.name'));
        $logger->pushHandler((new \Monolog\Handler\StreamHandler(
            $this->config('logger.path'),
            $this->config('logger.level')
        ))->setFormatter(new \Monolog\Formatter\LineFormatter()));
        return $logger;
    }

    public function getRouter(): RouterInterface
    {
        return new SymfonyRouter($this->config('site.url'));
    }

    public function getUrlGenerator(): UrlGeneratorInterface
    {
        return $this->getRouter()->getGenerator();
    }

    public function get404Handler(): ControllerInterface
    {
        return new NotFoundHandlerController();
    }

    public function get500Handler(): ControllerInterface
    {
        return new ErrorHandlerController($this->getLogger());
    }

    public function newRequestValidatorErrorHandler(): Closure
    {
        return function (
            ServerRequestInterface $request,
            ValidationFailedException $validationFailedException
        ) : ResponseInterface {
            return new JsonResponse([
                'validation_errors' => array_map(function (array $errors) {
                        return array_keys($errors);
                    }, $validationFailedException->getValidationErrors()),
            ], 400);
        };
    }

    public function getDatabase(): PDO
    {
        return new PDO(
            $this->config('db.dsn') . ';dbname=' . $this->config('db.dbname'),
            $this->config('db.user'),
            $this->config('db.pass'),
            [
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]
        );
    }

    public function getRbac(): Rbac
    {
        $rbac = new Rbac();
        
        $admin = new Role('admin');
        $admin->addPermission(AuthorizableControllerInterface::ROLE_ADMIN);
        $rbac->addRole($admin);

        $guest = new Role('guest');
        $guest->addPermission(AuthorizableControllerInterface::ROLE_PUBLIC);
        $rbac->addRole($guest);
        
        return $rbac;
    }

    public function getMailer(): Mailer
    {
        return new Mailer(Transport::fromDsn($this->config('mailer.dsn')));
    }

    public function getMailerTwigEnvironment(): Environment
    {
        return new Environment(new FilesystemLoader([__DIR__ . '/../templates/emails']));
    }

    public function getMailerTemplateFactory(): MailTemplateFactory
    {
        return new MailTemplateFactory(
            new TwigMailTemplate($this->getMailerTwigEnvironment(), 'new_ticket.twig'),
            new TwigMailTemplate($this->getMailerTwigEnvironment(), 'update_ticket.twig')
        );
    }

    public function getSessionStorage(): SessionStorageInterface
    {
        return new JwtSessionStorage(
            $this->config('site.url'),
            JwtConfiguration::forSymmetricSigner(
                new Sha512(),
                InMemory::plainText($this->config('session.secret_key'))
            )
        );
    }

    public function getSessionProcessor(): SessionProcessorInterface
    {
        return new SessionProcessor(
            $this->getSessionStorage(),
            $this->config('session.duration'),
            $this->config('session.refresh_after')
        );
    }

    public function getAuthenticationService(): AuthenticationServiceInterface
    {
        return new AuthenticationService(
            $this->getUserRepository(),
            $this->getRbac(),
            $this->config('password.algo'),
            $this->config('password.options')
        );
    }

    public function getSendMailService(): SendMailServiceInterface
    {
        return new SmtpSendMailService(
            $this->getTicketMailingRepository(),
            $this->getMailer(),
            $this->getMailerTemplateFactory(),
            EmailAddressValue::get($this->config('mailer.from')),
            $this->config('site.title')
        );
    }

    public function getFetchMailService(): FetchMailServiceInterface
    {
        return new PhpImapFetchMailService();
    }

    public function getUserRepository(): UserRepository
    {
        return new UserRepository($this->getDatabase(), $this->getRbac());
    }

    public function getTicketRepository(): TicketRepository
    {
        return new TicketRepository(
            $this->getDatabase(),
            $this->getStatusRepository(),
            $this->getDepartmentRepository()
        );
    }

    public function getStatusRepository(): StatusRepository
    {
        return new StatusRepository($this->getDatabase());
    }

    public function getDepartmentRepository(): DepartmentRepository
    {
        return new DepartmentRepository($this->getDatabase());
    }

    public function getMailboxRepository(): MailboxRepository
    {
        return new MailboxRepository($this->getDatabase(), $this->getDepartmentRepository());
    }

    public function getTicketMailingRepository(): TicketMailingRepository
    {
        return new TicketMailingRepository($this->getDatabase(), $this->getTicketRepository());
    }
}
