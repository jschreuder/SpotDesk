<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Service\Validator;

use Laminas\Validator\AbstractValidator;
use RuntimeException;

final class TypeValidator extends AbstractValidator
{
    const WRONG_TYPE = 'wrongType';

    /** @var array<string, string> */
    protected $messageTemplates = [
        self::WRONG_TYPE   => 'Wrong type given.',
    ];

    public function __construct($options = [])
    {
        if (! is_array($options)) {
            $options     = func_get_args();
            $temp['type'] = array_shift($options);

            $options = $temp;
        }

        if (!isset($options['type'])) {
            throw new RuntimeException('TypeValidator must be given a type to validate against.');
        }

        parent::__construct($options);
    }

    private function getRequiredType() : string
    {
        return $this->options['type'];
    }

    public function isValid(mixed $value) : bool
    {
        if (!is_a($value, $this->getRequiredType())) {
            $this->error(self::WRONG_TYPE);
            return false;
        }
        return true;
    }
}
