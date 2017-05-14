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
    private $storage;

    /** @var  int */
    private $duration;

    /** @var  float between 0 and 1, after how much of the duration a session should be refreshed */
    private $refreshAfter;

    public function __construct(
        SessionStorageInterface $storage,
        int $duration = 3600,
        float $refreshAfter = .5
    )
    {
        $this->storage = $storage;
        $this->duration = $duration;
        $this->refreshAfter = $refreshAfter;
    }

    public function processRequest(ServerRequestInterface $request) : ServerRequestInterface
    {
        $sessionId = $request->getHeaderLine(self::SESSION_KEY) ?? '';
        $session = $this->storage->load($sessionId);
        return $request->withAttribute('session', $session);
    }

    public function processResponse(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface
    {
        /** @var  Session $session */
        $session = $request->getAttribute('session');
        if (!$session || $session->isEmpty()) {
            return $response;
        }

        $refreshTimeframe = intval($this->duration * $this->refreshAfter);
        if (!$this->storage->needsRefresh($session, $refreshTimeframe)) {
            return $response;
        };

        return $response->withHeader(
            self::SESSION_KEY,
            $this->storage->create($session->toArray(), $this->duration)
        );
    }
}
