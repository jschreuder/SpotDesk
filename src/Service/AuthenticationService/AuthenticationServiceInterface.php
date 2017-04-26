<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Service\AuthenticationService;

use jschreuder\Middle\Session\SessionInterface;
use jschreuder\SpotDesk\Entity\User;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface AuthenticationServiceInterface
{
    /**
     * Creates a new user, stores it in the database and returns it.
     */
    public function createUser(string $email, string $displayName, string $password) : User;

    /**
     * Generates response based on the success of the login.
     */
    public function login(string $email, string $password) : ResponseInterface;

    /**
     * Returns Session object if the session ID found in the $authorizationHeader is valid,
     * null otherwise.
     */
    public function getSession(ServerRequestInterface $request) : ?SessionInterface;

    /**
     * Adds a session ID to the response if it is new or requires a refresh,
     * just returns the given response otherwise.
     */
    public function attachSession(ResponseInterface $response, SessionInterface $session) : ResponseInterface;
}
