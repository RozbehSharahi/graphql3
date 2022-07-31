<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Node\Nested;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use RozbehSharahi\Graphql3\Exception\GraphqlException;

/**
 * A registry for nested nodes.
 *
 * The registry loads nested nodes via container on runtime instead of compile time.
 *
 * The nested node registry can be used, when facing issues with circular references between types and nodes.
 */
class NestedNodeRegistry
{
    public function __construct(protected ContainerInterface $container)
    {
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $nestedNode
     *
     * @return T
     */
    public function get(string $nestedNode): object
    {
        try {
            return $this->container->get($nestedNode);
        } catch (NotFoundExceptionInterface|ContainerExceptionInterface $e) {
            throw new GraphqlException('Could not get nested node by name: '.$nestedNode);
        }
    }
}
