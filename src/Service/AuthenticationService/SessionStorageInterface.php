<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Service\AuthenticationService;

use jschreuder\Middle\Session\SessionInterface;

interface SessionStorageInterface
{
    /**
     * Creates a session with the user ID
     */
    public function create(array $sessionData, int $sessionDuration) : string;

    /**
     * Load session based on sessionData
     */
    public function load(string $sessionData) : SessionInterface;

    /**
     * Returns if the session needs a refresh. Because its data has changed or
     * when it's within the given timeframe of expiration.
     */
    public function needsRefresh(SessionInterface $session, int $refreshTimeframe) : bool;
}
