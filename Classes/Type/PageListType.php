<?php

namespace RozbehSharahi\Graphql3\Type;

use Closure;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode as Node;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNodeCollection as Collection;
use RozbehSharahi\Graphql3\Domain\Model\ListRequest;
use RozbehSharahi\Graphql3\Resolver\PageListResolver;

class PageListType extends ObjectType
{
    public function __construct(protected PageListResolver $listResolver, protected PageType $pageType)
    {
        parent::__construct([
            'name' => 'PageList',
            'fields' => $this->getFieldsClosure(),
        ]);
    }

    private function getFieldsClosure(): Closure
    {
        return function () {
            return Collection::create()
                ->add(
                    Node::create('count')
                        ->withType(Type::int())
                        ->withResolver(fn (ListRequest $request) => $this->listResolver->resolveCount($request))
                )
                ->add(
                    Node::create('items')
                        ->withType(Type::listOf($this->pageType))
                        ->withResolver(fn (ListRequest $request) => $this->listResolver->resolveItems($request))
                )
                ->toArray()
            ;
        };
    }
}
