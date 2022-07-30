<?php

namespace RozbehSharahi\Graphql3\Node;

use GraphQL\Type\Definition\Type;
use RozbehSharahi\Graphql3\Domain\Model\Context;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlArgument;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlArgumentCollection;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;
use RozbehSharahi\Graphql3\Resolver\PageResolver;
use RozbehSharahi\Graphql3\Type\PageType;

class PageNode implements NodeInterface
{
    protected string $name = 'page';

    protected Context $context;

    /**
     * @param iterable<PageNodeExtenderInterface> $extenders
     */
    public function __construct(
        protected PageType $pageType,
        protected PageResolver $pageResolver,
        protected iterable $extenders
    ) {
        $this->context = new Context();
    }

    public function forSlug(): self
    {
        return $this
            ->withName('pageBySlug')
            ->withContext(new Context([Context::TAG_PAGE_RESOLVE_BY_SLUG]));
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

    public function getContext(): Context
    {
        return $this->context;
    }

    public function withContext(Context $context): self
    {
        $clone = clone $this;
        $clone->context = $context;

        return $clone;
    }

    public function getGraphqlNode(): GraphqlNode
    {
        $isPageBySlug = $this->context->hasTag(Context::TAG_PAGE_RESOLVE_BY_SLUG);

        $identifierName = !$isPageBySlug ? 'uid' : 'slug';
        $identifierType = !$isPageBySlug ? Type::int() : Type::string();

        $arguments = GraphqlArgumentCollection::create([
            GraphqlArgument::create($identifierName)->withType(Type::nonNull($identifierType)),
            GraphqlArgument::create('publicRequest')->withType(Type::boolean())->withDefaultValue(true),
        ]);

        foreach ($this->extenders as $extender) {
            $arguments = $extender->extendArguments($arguments);
        }

        return GraphqlNode::create($this->name)
            ->withType($this->pageType)
            ->withArguments($arguments)
            ->withResolver($this->pageResolver->withContext($this->context)->getCallable());
    }
}
