<?php

namespace RozbehSharahi\Graphql3\Type;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNodeCollection;
use RozbehSharahi\Graphql3\Resolver\PageResolver;

class PageType extends ObjectType
{
    /**
     * @param iterable<PageTypeExtenderInterface> $extenders
     */
    public function __construct(protected PageResolver $pageResolver, protected iterable $extenders)
    {
        parent::__construct([
            'name' => 'Page',
            'fields' => function () {
                $nodes = GraphqlNodeCollection::create()
                    ->add(
                        GraphqlNode::create('uid')
                            ->withType(Type::int())
                            ->withResolver(fn (array $page) => $page['uid'])
                    )
                    ->add(
                        GraphqlNode::create('title')
                            ->withType(Type::string())
                            ->withResolver(fn (array $page) => $page['title'])
                    )
                    ->add(
                        GraphqlNode::create('slug')
                            ->withType(Type::string())
                            ->withResolver(fn (array $page) => $page['slug'])
                    )
                    ->add(
                        GraphqlNode::create('parent')
                            ->withType($this)
                            // @todo refactor this
                            ->withResolver(
                                fn (array $page) => !empty($page['pid'])
                                    ? $this->pageResolver->getCallable()($page, ['uid' => $page['pid']]) :
                                    null
                            )
                    );

                foreach ($this->extenders as $extender) {
                    $nodes = $extender->extendNodes($nodes);
                }

                return $nodes->toArray();
            },
        ]);
    }
}
