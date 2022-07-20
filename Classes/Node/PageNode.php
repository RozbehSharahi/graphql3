<?php

namespace RozbehSharahi\Graphql3\Node;

use GraphQL\Type\Definition\Type;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlArgument;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlArgumentCollection;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;
use RozbehSharahi\Graphql3\Domain\Model\PageResolverContext;
use RozbehSharahi\Graphql3\Resolver\PageResolver;
use RozbehSharahi\Graphql3\Type\PageType;

class PageNode implements NodeInterface
{
    protected string $name = 'page';
    protected string $targetIdentifier = 'uid';
    protected Type $targetIdentifierType;

    /**
     * @param iterable<PageNodeExtenderInterface> $extenders
     */
    public function __construct(
        protected PageType $pageType,
        protected PageResolver $pageResolver,
        protected iterable $extenders
    ) {
        $this->targetIdentifierType = Type::int();
    }

    public function forSlug(): self
    {
        return $this
            ->withName('pageBySlug')
            ->withTargetIdentifier('slug')
            ->withTargetIdentifierType(Type::string());
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

    public function getTargetIdentifier(): string
    {
        return $this->targetIdentifier;
    }

    public function withTargetIdentifier(string $targetIdentifier): self
    {
        $clone = clone $this;
        $clone->targetIdentifier = $targetIdentifier;

        return $clone;
    }

    public function getTargetIdentifierType(): Type
    {
        return $this->targetIdentifierType;
    }

    public function withTargetIdentifierType(Type $targetIdentifierType): self
    {
        $clone = clone $this;
        $clone->targetIdentifierType = $targetIdentifierType;

        return $clone;
    }

    public function getGraphqlNode(): GraphqlNode
    {
        $arguments = GraphqlArgumentCollection::create([
            GraphqlArgument::create($this->targetIdentifier)->withType(Type::nonNull($this->targetIdentifierType)),
        ]);

        foreach ($this->extenders as $extender) {
            $arguments = $extender->extendArguments($arguments);
        }

        return GraphqlNode::create($this->name)
            ->withType($this->pageType)
            ->withArguments($arguments)
            ->withResolver(
                $this
                    ->pageResolver
                    ->withContext(new PageResolverContext($this->targetIdentifier))
                    ->getCallable()
            );
    }
}
