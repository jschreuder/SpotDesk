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
    public function createUser(string $email, string $displayName, string $password): User;

    /**
     * Should return a string session ID or throw an AuthenticationFailedException when user
     * credentials are incorrect.
     */
    public function login(string $email, string $password): string;

    /**
     * Returns Session object if the session ID found in the $authorizationHeader is valid,
     * null otherwise.
     */
    public function checkLogin(ServerRequestInterface $request, string $authorizationHeader): ?SessionInterface;

    /**
     * Adds a fresh session ID to the response in the $authorizationHeader if it requires a
     * refresh, just returns the given response otherwise.
     */
    public function refreshSession(
        ResponseInterface $response,
        string $authorizationHeader,
        SessionInterface $session
    ): ResponseInterface;
}
