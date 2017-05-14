<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Controller;

use jschreuder\Middle\Controller\ControllerInterface;
use jschreuder\SpotDesk\Service\AuthorizationService\AuthorizableControllerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;

class AppInitController implements ControllerInterface, AuthorizableControllerInterface
{
    /** @var  string */
    private $siteTitle;

    /** @var  string */
    private $siteUrl;

    public function __construct($siteTitle, $siteUrl)
    {
        $this->siteTitle = $siteTitle;
        $this->siteUrl = $siteUrl;
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
