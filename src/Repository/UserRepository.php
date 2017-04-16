<?php declare(strict_types=1);

namespace jschreuder\SpotDesk\Repository;

use jschreuder\SpotDesk\Entity\User;
use jschreuder\SpotDesk\Value\EmailAddressValue;

class UserRepository
{
    /** @var  \PDO */
    private $db;

    public function __construct(\PDO $db)
    {
        $this->db = $db;
    }

    public function createUser(User $user): void
    {
        $query = $this->db->prepare("
            INSERT INTO `users` (`email`, `display_name`, `password`)
            VALUES (:email, :display_name, :password)
        ");
        $query->execute([
            'email' => $user->getEmail()->toString(),
            'display_name' => $user->getDisplayName(),
            'password' => $user->getPassword(),
        ]);
    }

    private function arrayToUser(array $row): User
    {
        return new User(
            EmailAddressValue::get($row['email']),
            $row['display_name'],
            $row['password'],
            $row['totpSecret']
        );
    }

    public function getUserByEmail(EmailAddressValue $email): User
    {
        $query = $this->db->prepare("SELECT * FROM `users` WHERE `email` = :email");
        $query->execute(['email' => $email->toString()]);

        if ($query->rowCount() !== 1) {
            throw new \OutOfBoundsException('No user found with e-mail address: ' . $email->toString());
        }

        return $this->arrayToUser($query->fetch(\PDO::FETCH_ASSOC));
    }

    public function updatePassword(User $user, string $newPassword): void
    {
        $query = $this->db->prepare("
            UPDATE `users` 
            SET `password` = :password 
            WHERE `email` = :email
        ");
        $query->execute(['password' => $newPassword, 'email' => $user->getEmail()->toString()]);

        if ($query->rowCount() !== 1) {
            throw new \RuntimeException('Failed to update password for user: ' . $user->getEmail()->toString());
        }
        $user->setPassword($newPassword);
    }
}
