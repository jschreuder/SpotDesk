<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Repository;

use jschreuder\SpotDesk\Collection\UserCollection;
use jschreuder\SpotDesk\Entity\Department;
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

    public function createUser(User $user) : void
    {
        $query = $this->db->prepare("
            INSERT INTO `users` (`email`, `display_name`, `password`, `active`)
            VALUES (:email, :display_name, :password, TRUE)
        ");
        $query->execute([
            'email' => $user->getEmail()->toString(),
            'display_name' => $user->getDisplayName(),
            'password' => $user->getPassword(),
        ]);
    }

    private function arrayToUser(array $row) : User
    {
        return new User(
            EmailAddressValue::get($row['email']),
            $row['display_name'],
            $row['password'],
            boolval($row['active'])
        );
    }

    public function getUsers() : UserCollection
    {
        $query = $this->db->query("SELECT * FROM `users`");
        $users = new UserCollection();
        while ($row = $query->fetch(\PDO::FETCH_ASSOC)) {
            $users->push($this->arrayToUser($row));
        }
        return $users;
    }

    public function getUserByEmail(EmailAddressValue $email) : User
    {
        $query = $this->db->prepare("SELECT * FROM `users` WHERE `email` = :email");
        $query->execute(['email' => $email->toString()]);

        if ($query->rowCount() !== 1) {
            throw new \OutOfBoundsException('No user found with e-mail address: ' . $email->toString());
        }

        return $this->arrayToUser($query->fetch(\PDO::FETCH_ASSOC));
    }

    public function updateUser(User $user) : void
    {
        $query = $this->db->prepare("
            UPDATE `users`
            SET `display_name` = :display_name, active = :active
            WHERE `email` = :email
        ");
        $query->execute([
            'display_name' => $user->getDisplayName(),
            'email' => $user->getEmail()->toString(),
            'active' => intval($user->isActive()),
        ]);

        if ($query->rowCount() !== 1) {
            throw new \RuntimeException('Failed to update user: ' . $user->getEmail()->toString());
        }
    }

    public function updatePassword(User $user, string $newPassword) : void
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

    public function assignUserToDepartment(User $user, Department $department) : void
    {
        $query = $this->db->prepare("
            INSERT INTO `users_departments` (`email`, `department_id`)
            VALUES (:email, :department_id)
        ");
        $query->execute([
            'email' => $user->getEmail()->toString(),
            'department_id' => $department->getId()->getBytes(),
        ]);
    }

    public function removeUserFromDepartment(User $user, Department $department) : void
    {
        $query = $this->db->prepare("
            DELETE FROM `users_departments`
            WHERE `email` = :email AND `department_id` = :department_id
        ");
        $query->execute([
            'email' => $user->getEmail()->toString(),
            'department_id' => $department->getId()->getBytes(),
        ]);
    }

    public function deleteUser(User $user) : void
    {
        $query = $this->db->prepare("
            DELETE FROM `users`
            WHERE `email` = :email
        ");
        $query->execute(['email' => $user->getEmail()->toString()]);
    }
}
