<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Controller;

use jschreuder\Middle\Controller\ControllerInterface;
use jschreuder\SpotDesk\Service\AuthorizationService\AuthorizableControllerInterface;
use Laminas\Diactoros\Response as Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AngularInitController implements ControllerInterface, AuthorizableControllerInterface
{
    public function __construct(
        private string $siteTitle, 
        private string $siteUrl
    )
    {
    }

    public function getRequiredPermission() : string
    {
        return AuthorizableControllerInterface::ROLE_PUBLIC;
    }

    public function execute(ServerRequestInterface $request) : ResponseInterface
    {
        $siteTitle = $this->siteTitle;
        $siteUrl = $this->siteUrl;
        $generator = function () use ($siteTitle, $siteUrl): string {
            ob_start();
            require __DIR__ . '/../../templates/template.php';
            $rendered = ob_get_contents();
            ob_end_clean();
            return $rendered;
        };
        $response = new Response();
        $response->getBody()->write($generator());
        return $response;
    }
}
