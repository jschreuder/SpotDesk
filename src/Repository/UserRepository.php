<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Repository;

use jschreuder\SpotDesk\Collection\UserCollection;
use jschreuder\SpotDesk\Entity\Department;
use jschreuder\SpotDesk\Entity\User;
use jschreuder\SpotDesk\Exception\SpotDeskException;
use jschreuder\SpotDesk\Value\EmailAddressValue;
use Laminas\Permissions\Rbac\Rbac;
use OutOfBoundsException;
use PDO;

class UserRepository
{
    public function __construct(
        private PDO $db, 
        private Rbac $rbac
    )
    {
    }

    public function createUser(User $user) : void
    {
        $query = $this->db->prepare("
            INSERT INTO `users` (`email`, `display_name`, `password`, `role`, `active`)
            VALUES (:email, :display_name, :password, :role, TRUE)
        ");
        $query->execute([
            'email' => $user->getEmail()->toString(),
            'display_name' => $user->getDisplayName(),
            'password' => $user->getPassword(),
            'role' => $user->getRole()->getName(),
        ]);
    }

    private function arrayToUser(array $row) : User
    {
        return new User(
            EmailAddressValue::get($row['email']),
            $row['display_name'],
            $row['password'],
            $this->rbac->getRole($row['role']),
            boolval($row['active'])
        );
    }

    private function statementToUserCollection(\PDOStatement $statement) : UserCollection
    {
        $users = new UserCollection();
        while ($row = $statement->fetch(\PDO::FETCH_ASSOC)) {
            $users->push($this->arrayToUser($row));
        }
        return $users;
    }

    public function getUsers() : UserCollection
    {
        return $this->statementToUserCollection(
            $this->db->query("SELECT * FROM `users`")
        );
    }

    public function getUsersForDepartment(Department $department) : UserCollection
    {
        $query = $this->db->prepare("
            SELECT u.*
            FROM `users` u
            INNER JOIN `users_departments` ud ON u.`email` = ud.`email` AND ud.department_id = :department_id
            WHERE u.`active` IS TRUE
        ");
        $query->execute(['department_id' => $department->getId()->getBytes()]);
        return $this->statementToUserCollection($query);
    }

    public function getUserByEmail(EmailAddressValue $email) : User
    {
        $query = $this->db->prepare("SELECT * FROM `users` WHERE `email` = :email");
        $query->execute(['email' => $email->toString()]);

        if ($query->rowCount() !== 1) {
            throw new OutOfBoundsException('No user found with e-mail address: ' . $email->toString());
        }

        return $this->arrayToUser($query->fetch(\PDO::FETCH_ASSOC));
    }

    public function updateUser(User $user) : void
    {
        $query = $this->db->prepare("
            UPDATE `users`
            SET `display_name` = :display_name, role = :role, active = :active
            WHERE `email` = :email
        ");
        $query->execute([
            'display_name' => $user->getDisplayName(),
            'email' => $user->getEmail()->toString(),
            'role' => $user->getRole()->getName(),
            'active' => intval($user->isActive()),
        ]);

        if ($query->rowCount() !== 1) {
            throw new SpotDeskException('Failed to update user: ' . $user->getEmail()->toString());
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
            throw new SpotDeskException('Failed to update password for user: ' . $user->getEmail()->toString());
        }
        $user->setPassword($newPassword);
    }

    public function assignUserToDepartment(User $user, Department $department) : void
    {
        $query = $this->db->prepare("
            INSERT INTO `users_departments` (`email`, `department_id`)
            VALUES (:email, :department_id)
            ON DUPLICATE KEY UPDATE email = :email, department_id = :department_id
        ");
        $query->execute([
            'email' => $user->getEmail()->toString(),
            'department_id' => $department->getId()->getBytes(),
        ]);

        if ($query->rowCount() !== 1) {
            throw new SpotDeskException(
                'User "' . $user->getEmail()->toString() . '" was already assigned to department "' .
                $department->getName() . '".'
            );
        }
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

        if ($query->rowCount() !== 1) {
            throw new SpotDeskException(
                'User "' . $user->getEmail()->toString() . '" could not be removed from department "' .
                $department->getName() . '", probably because it was not assigned to it.'
            );
        }
    }

    public function deleteUser(User $user) : void
    {
        $query = $this->db->prepare("
            DELETE FROM `users`
            WHERE `email` = :email
        ");
        $query->execute(['email' => $user->getEmail()->toString()]);

        if ($query->rowCount() !== 1) {
            throw new SpotDeskException(
                'User "' . $user->getEmail()->toString() . '" could not be deleted, possibly did no exist."'
            );
        }
    }
}
