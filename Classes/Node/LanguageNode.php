<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Node;

use GraphQL\Type\Definition\Type;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlArgument;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlArgumentCollection;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;
use RozbehSharahi\Graphql3\Resolver\LanguageResolver;
use RozbehSharahi\Graphql3\Type\LanguageType;

class LanguageNode implements NodeInterface
{
    protected string $name = 'language';

    public function __construct(protected LanguageType $languageType, protected LanguageResolver $languageResolver)
    {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function withName(string $name): self
    {
        $clone = clone $this;
        $clone->name = $name;

        return $clone;
    }

    public function getGraphqlNode(): GraphqlNode
    {
        return GraphqlNode::create($this->name)
            ->withType($this->languageType)
            ->withArguments(GraphqlArgumentCollection::create([
                GraphqlArgument::create('id')->withType(Type::nonNull(Type::int())),
            ]))
            ->withResolver($this->languageResolver->getCallable())
        ;
    }
}
