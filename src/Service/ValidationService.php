<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Service;

use jschreuder\Middle\Exception\ValidationFailedException;
use Laminas\Validator\ValidatorInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

final class ValidationService
{
    private static function runValidation(array $input, array $validators, array $optionalKeys)
    {
        $errors = [];
        foreach ($validators as $key => $validator) {
            // Always error when invalid validators are given
            if (!$validator instanceof ValidatorInterface) {
                throw new RuntimeException('Invalid validator', 500);
            }
            // Skip optional fields only when not in input or null, all other inputs are considered
            if (in_array($key, $optionalKeys) && (!isset($input[$key]) || is_null($key))) {
                continue;
            }
            // Register errors when validation fails
            if (!$validator->isValid($input[$key])) {
                $errors[$key] = $validator->getMessages();
            }
        }
        // Validation failed when one or more errors were given
        if ($errors) {
            throw new ValidationFailedException($errors);
        }
    }

    public static function validate(
        ServerRequestInterface $request, 
        array $validators,
        array $optionalKeys = []
    ) : void
    {
        self::runValidation((array) $request->getParsedBody(), $validators, $optionalKeys);
    }

    public static function validateQuery(
        ServerRequestInterface $request, 
        array $validators,
        array $optionalKeys = []
    ) : void
    {
        self::runValidation((array) $request->getQueryParams(), $validators, $optionalKeys);
    }
}
