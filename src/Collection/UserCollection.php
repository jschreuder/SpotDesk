<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Collection;

use ArrayAccess;
use Countable;
use Iterator;
use jschreuder\SpotDesk\Entity\User;
use OutOfBoundsException;

class UserCollection implements ArrayAccess, Countable, Iterator
{
    use CollectionTrait;

    public function __construct(User ...$users)
    {
        foreach ($users as $user) {
            $this->push($user);
        }
    }

    public function push(User $user) : void
    {
        $this->collection[$user->getEmail()->toString()] = $user;
    }

    public function current() : User
    {
        return current($this->collection);
    }

    public function offsetGet($email) : User
    {
        if (!$this->offsetExists($email)) {
            throw new OutOfBoundsException('No user with e-mail: ' . $email);
        }
        return $this->collection[$email];
    }
}
