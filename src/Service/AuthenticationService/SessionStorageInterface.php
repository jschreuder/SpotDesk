<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Service\AuthenticationService;

use jschreuder\Middle\Session\SessionInterface;

interface SessionStorageInterface
{
    /**
     * Creates a session with the user ID
     */
    public function create(string $userId, int $sessionDuration) : string;

    /**
     * Load session based on sessionData
     */
    public function load(string $sessionData) : ?SessionInterface;

    /**
     * Returns if the session is within the given timeframe of expiration, in
     * which case a refresh is necessary
     */
    public function needsRefresh(SessionInterface $session, int $refreshTimeframe) : bool;
}
