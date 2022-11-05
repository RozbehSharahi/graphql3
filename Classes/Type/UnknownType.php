<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Type;

use GraphQL\Type\Definition\ScalarType;
use RozbehSharahi\Graphql3\Exception\NotImplementedException;

/**
 * @note Please do not use as far as possible !
 */
class UnknownType extends ScalarType
{
    public $description = 'Arbitrary data encoded in JavaScript Object Notation. See https://www.json.org.';

    /**
     * @return array<string, mixed>|string
     */
    public function serialize($value): array|string
    {
        return $value;
    }

    /**
     * @return array<string, mixed>
     */
    public function parseValue($value): array
    {
        throw new NotImplementedException('Cannot and maybe should not parse free-object-type.');
    }

    public function parseLiteral($valueNode, ?array $variables = null)
    {
        throw new NotImplementedException('Cannot and maybe should not parse literals of free-object-type.');
    }
}
