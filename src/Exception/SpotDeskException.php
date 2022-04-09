<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Exception;

use RuntimeException;
use Throwable;

class SpotDeskException extends RuntimeException
{
    public function __construct($message = '', Throwable $previous = null)
    {
        parent::__construct($message, 500, $previous);
    }
}
