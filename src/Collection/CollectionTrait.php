<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Collection;

use jschreuder\SpotDesk\Exception\SpotDeskException;

trait CollectionTrait
{
    private $collection = [];

    public function next() : void
    {
        next($this->collection);
    }

    public function key() : string
    {
        return key($this->collection);
    }

    public function valid() : bool
    {
        return key($this->collection) !== null;
    }

    public function rewind() : void
    {
        reset($this->collection);
    }

    public function offsetExists($key) : bool
    {
        return isset($this->collection[strval($key)]);
    }

    public function offsetSet($key, $value) : void
    {
        throw new SpotDeskException('Collections cannot be modified as array');
    }

    public function offsetUnset($key) : void
    {
        throw new SpotDeskException('Collections cannot be modified as array');
    }

    public function count()
    {
        return count($this->collection);
    }

    public function toArray() : array
    {
        return $this->collection;
    }
}
