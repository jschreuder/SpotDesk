<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Service\Validator;

use Laminas\Permissions\Rbac\Rbac;
use Laminas\Validator\AbstractValidator;
use RuntimeException;

final class RoleValidator extends AbstractValidator
{
    const UNKNOWN_ROLE = 'unknownRole';

    /** @var array<string, string> */
    protected $messageTemplates = [
        self::UNKNOWN_ROLE   => 'Given role is not known by the system.',
    ];

    public function __construct($options = [])
    {
        if (! is_array($options)) {
            $options     = func_get_args();
            $temp['rbac'] = array_shift($options);

            $options = $temp;
        }

        if (!isset($options['rbac']) || !is_a($options['rbac'], Rbac::class)) {
            throw new RuntimeException('Valid Rbac instance must be provided to role validator.');
        }

        parent::__construct($options);
    }

    private function getRbac() : Rbac
    {
        return $this->options['rbac'];
    }

    public function isValid(mixed $value) : bool
    {
        if (!$this->getRbac()->hasRole($value)) {
            $this->error(self::UNKNOWN_ROLE);
            return false;
        }
        return true;
    }
}
