<?php
/**
 * Created by PhpStorm.
 * User: jschreuder
 * Date: 16-4-17
 * Time: 17:38
 */

namespace jschreuder\SpotDesk\Service;

use jschreuder\SpotDesk\Entity\User;

interface AuthenticationServiceInterface
{
    /**
     * Creates a new user, stores it in the database and returns it
     */
    public function createUser(string $email, string $password, string $displayName): User;

    /**
     * Should return a string session ID or throw an AuthenticationFailedException when user
     * credentials are incorrect.
     */
    public function login(string $email, string $password): string;

    /**
     * Returns boolean if the session ID is valid
     */
    public function checkLogin(string $sessionId): bool;

    /**
     * Return a fresh session ID if it requires a refresh, or null if it doesn't
     */
    public function refreshSession(string $sessionId): ?string;
}
