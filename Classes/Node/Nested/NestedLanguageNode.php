<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Node\Nested;

use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;
use RozbehSharahi\Graphql3\Domain\Model\ItemRequest;
use RozbehSharahi\Graphql3\Exception\GraphqlException;
use RozbehSharahi\Graphql3\Node\NodeInterface;
use RozbehSharahi\Graphql3\Resolver\LanguageResolver;
use RozbehSharahi\Graphql3\Type\LanguageType;

class NestedLanguageNode implements NodeInterface
{
    use NestedNodeTrait;

    protected \Closure $idResolver;

    public function __construct(protected LanguageType $languageType, protected LanguageResolver $languageResolver)
    {
    }

    public function getIdResolver(): \Closure
    {
        return $this->idResolver;
    }

    public function withIdResolver(\Closure $languageIdResolver): self
    {
        $clone = clone $this;
        $clone->idResolver = $languageIdResolver;

        return $clone;
    }

    public function getGraphqlNode(): GraphqlNode
    {
        if (empty($this->idResolver)) {
            throw new GraphqlException('Did you forget to define a language id resolver?');
        }

        return GraphqlNode::create($this->name)
            ->withType($this->languageType)
            ->withResolver(
                fn ($value) => $this->languageResolver->resolve(new ItemRequest(['id' => ($this->idResolver)($value)]))
            )
        ;
    }
}
