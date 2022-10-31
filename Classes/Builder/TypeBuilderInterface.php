<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Builder;

use GraphQL\Type\Definition\ObjectType;

/**
 * Type builders.
 *
 * - every type builder should have a cache property, as webonyx/graphql only accepts one instance per type
 * - every type builder has a build method that returns the type either from cache or on-the-fly generated
 * - every type builder has a method to flush the cache for testing
 */
interface TypeBuilderInterface
{
    public static function flushCache(): void;

    public function build(): ObjectType;
}
