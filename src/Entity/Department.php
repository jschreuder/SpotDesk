<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Entity;

use Ramsey\Uuid\UuidInterface;

class Department
{
    /** @var  UuidInterface */
    private $id;

    /** @var  string */
    private $name;

    /** @var  ?Department */
    private $parent;

    public function __construct(UuidInterface $id, string $name, ?Department $parent)
    {
        $this->id = $id;
        $this->name = $name;
        $this->parent = $parent;
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getParent(): ?Department
    {
        return $this->parent;
    }

    public function setParent(?Department $parent): void
    {
        $this->parent = $parent;
    }
}
