<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Service\SessionService;

use jschreuder\Middle\Session\Session;
use jschreuder\Middle\Session\SessionProcessorInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class SessionProcessor implements SessionProcessorInterface
{
    const SESSION_KEY = 'SpotDesk-Session';

    /** @var  SessionStorageInterface */
    private $sessionStorage;

    /** @var  int */
    private $sessionDuration;

    /** @var  float between 0 and 1, after how much of the duration a session should be refreshed */
    private $sessionRefreshAfter;

    public function __construct(
        SessionStorageInterface $sessionStorage,
        int $sessionDuration = 3600,
        float $sessionRefreshAfter = .5
    )
    {
        $this->sessionStorage = $sessionStorage;
        $this->sessionDuration = $sessionDuration;
        $this->sessionRefreshAfter = $sessionRefreshAfter;
    }

    public function processRequest(ServerRequestInterface $request) : ServerRequestInterface
    {
        $sessionId = $request->getHeaderLine(self::SESSION_KEY) ?? '';
        $session = $this->sessionStorage->load($sessionId);
        return $request->withAttribute('session', $session);
    }

    public function processResponse(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface
    {
        /** @var  Session $session */
        $session = $request->getAttribute('session');
        if (!$session || $session->isEmpty()) {
            return $response;
        }

        $refreshTimeframe = intval($this->sessionDuration * $this->sessionRefreshAfter);
        if (!$this->sessionStorage->needsRefresh($session, $refreshTimeframe)) {
            return $response;
        };

        return $response->withHeader(
            self::SESSION_KEY,
            $this->sessionStorage->create($session->toArray(), $this->sessionDuration)
        );
    }
}
