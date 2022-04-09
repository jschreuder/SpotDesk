<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Service;

use Psr\Http\Message\ServerRequestInterface;

final class FilterService
{
    private function runFilters(array $input, array $filters) : array
    {
        foreach ($filters as $key => $filter) {
            if (isset($input[$key]) && is_callable($filter)) {
                $input[$key] = $filter($input[$key]);
            }
        }
        return $input;
    }

    public static function filter(ServerRequestInterface $request, array $filters) : ServerRequestInterface
    {
        return $request->withParsedBody(self::runFilters((array) $request->getParsedBody(), $filters));
    }

    public static function filterQuery(ServerRequestInterface $request, array $filters) : ServerRequestInterface
    {
        return $request->withQueryParams(self::runFilters((array) $request->getQueryParams(), $filters));
    }
}
