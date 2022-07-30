<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Type;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use RozbehSharahi\Graphql3\Domain\Model\FormattedTimestamp;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlArgument;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlArgumentCollection;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNodeCollection;
use RozbehSharahi\Graphql3\Resolver\RecordResolver;

class PageType extends ObjectType
{
    /**
     * @param iterable<PageTypeExtenderInterface> $extenders
     */
    public function __construct(protected RecordResolver $recordResolver, protected iterable $extenders)
    {
        parent::__construct([
            'name' => 'Page',
            'fields' => $this->getFieldClosure(),
        ]);
    }

    private function getFieldClosure(): \Closure
    {
        return function () {
            $nodes = GraphqlNodeCollection::create()
                ->add(GraphqlNode::create('uid')->withType(Type::int())->withResolver(fn (array $page) => $page['uid']))
                ->add(GraphqlNode::create('title')->withResolver(fn (array $page) => $page['title']))
                ->add(GraphqlNode::create('slug')->withResolver(fn (array $page) => $page['slug']))
                ->add($this->createDateTimeNode('updatedAt', 'tstamp'))
                ->add($this->createDateTimeNode('createdAt', 'crdate'))
                ->add(
                    GraphqlNode::create('parent')->withType($this)->withResolver(
                        fn (array $page) => $this->recordResolver->resolve('pages', $page['pid'])
                    )
                )
                ->add(
                    GraphqlNode::create('children')->withType(Type::listOf($this))->withResolver(
                        fn (array $page) => $this->recordResolver->resolveManyByPid('pages', $page['uid'])
                    )
                )
            ;

            foreach ($this->extenders as $extender) {
                $nodes = $extender->extendNodes($nodes);
            }

            return $nodes->toArray();
        };
    }

    protected function createDateTimeNode(string $name, string $property): GraphqlNode
    {
        return GraphqlNode::create($name)
            ->withArguments(
                GraphqlArgumentCollection::create([GraphqlArgument::create('format')->withDefaultValue('Y-m-d h:i')])
            )
            ->withResolver(
                fn (array $page, array $args) => new FormattedTimestamp($page[$property], $args['format'])
            )
        ;
    }
}
