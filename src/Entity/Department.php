<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Entity;

use jschreuder\SpotDesk\Value\EmailAddressValue;
use Ramsey\Uuid\UuidInterface;

class Department
{
    public function __construct(
        private UuidInterface $id, 
        private string $name, 
        private ?Department $parent, 
        private EmailAddressValue $email
    )
    {
    }

    public function getId() : UuidInterface
    {
        return $this->id;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function setName(string $name) : void
    {
        $this->name = $name;
    }

    public function getParent() : ?Department
    {
        return $this->parent;
    }

    public function setParent(?Department $parent) : void
    {
        $this->parent = $parent;
    }

    public function getEmail() : EmailAddressValue
    {
        return $this->email;
    }

    public function setEmail(EmailAddressValue $email) : void
    {
        $this->email = $email;
    }
}
