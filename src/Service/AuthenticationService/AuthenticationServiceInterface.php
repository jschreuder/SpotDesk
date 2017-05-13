<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Service\AuthenticationService;

use jschreuder\Middle\Session\SessionInterface;
use jschreuder\SpotDesk\Entity\User;

interface AuthenticationServiceInterface
{
    /**
     * Creates a new user, stores it in the database and returns it.
     */
    public function createUser(string $email, string $displayName, string $password, string $roleName) : User;

    /**
     * Fetches a user entity by its e-mailaddress.
     */
    public function fetchUser(string $email) : User;

    /**
     * Modifies the given user's password
     */
    public function changePassword(User $user, string $newPassword) : void;

    /**
     * Returns if the password matches the user's hash
     */
    public function checkPassword(User $user, string $password) : bool;

    /**
     * Adds userdata to session if login passes, returns boolean indicating
     * success or failure.
     */
    public function login(string $email, string $password, SessionInterface $session) : bool;
}
