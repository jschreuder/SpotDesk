<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Service;

use jschreuder\SpotDesk\Entity\User;
use jschreuder\SpotDesk\Value\EmailAddressValue;

class UserService
{
    /** @var  \PDO */
    private $db;

    public function __construct(\PDO $db)
    {
        $this->db = $db;
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
}
