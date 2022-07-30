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
use RozbehSharahi\Graphql3\Site\CurrentSite;

class PageType extends ObjectType
{
    /**
     * @param iterable<PageTypeExtenderInterface> $extenders
     */
    public function __construct(
        protected RecordResolver $recordResolver,
        protected LanguageType $languageType,
        protected CurrentSite $currentSite,
        protected iterable $extenders
    ) {
        parent::__construct([
            'name' => 'Page',
            'fields' => $this->getFieldClosure(),
        ]);
    }

    private function getFieldClosure(): \Closure
    {
        return function () {
            $nodes = GraphqlNodeCollection::create()
                ->add($this->createIntNode('uid', 'uid'))
                ->add($this->createStringNode('title', 'title'))
                ->add($this->createStringNode('title', 'title'))
                ->add($this->createDateTimeNode('updatedAt', 'tstamp'))
                ->add($this->createDateTimeNode('createdAt', 'crdate'))
                ->add($this->createBoolNode('deleted', 'deleted'))
                ->add($this->createBoolNode('hidden', 'hidden'))
                ->add($this->createDateTimeNode('startTime', 'starttime'))
                ->add($this->createDateTimeNode('endTime', 'endtime'))
                ->add($this->createIntNode('sorting', 'sorting'))
                ->add($this->createPageNode('parent', 'pid'))
                ->add($this->createPageChildrenNode('children', 'uid'))
                ->add($this->createLanguageNode())
            ;

            foreach ($this->extenders as $extender) {
                $nodes = $extender->extendNodes($nodes);
            }

            return $nodes->toArray();
        };
    }

    protected function createStringNode(string $name, string $property): GraphqlNode
    {
        return GraphqlNode::create($name)
            ->withType(Type::string())
            ->withResolver(fn (array $page) => $page[$property])
        ;
    }

    protected function createIntNode(string $name, string $property): GraphqlNode
    {
        return GraphqlNode::create($name)
            ->withType(Type::int())
            ->withResolver(fn (array $page) => $page[$property])
        ;
    }

    protected function createDateTimeNode(string $name, string $property): GraphqlNode
    {
        $arguments = GraphqlArgumentCollection::create([
            GraphqlArgument::create('format')->withDefaultValue('Y-m-d h:i'),
        ]);

        return GraphqlNode::create($name)
            ->withArguments($arguments)
            ->withResolver(fn (array $page, array $args) => new FormattedTimestamp($page[$property], $args['format']))
        ;
    }

    protected function createBoolNode(string $name, string $property): GraphqlNode
    {
        return GraphqlNode::create($name)
            ->withType(Type::boolean())
            ->withResolver(fn (array $page) => $page[$property])
        ;
    }

    protected function createPageNode(string $name, string $property): GraphqlNode
    {
        return GraphqlNode::create($name)->withType($this)->withResolver(
            fn (array $page) => $this->recordResolver->resolve('pages', $page[$property])
        );
    }

    protected function createPageChildrenNode(string $name, string $parentPageProperty): GraphqlNode
    {
        return GraphqlNode::create($name)->withType(Type::listOf($this))->withResolver(
            fn (array $page) => $this->recordResolver->resolveManyByPid('pages', $page[$parentPageProperty])
        );
    }

    private function createLanguageNode(): GraphqlNode
    {
        return GraphqlNode::create('language')->withType($this->languageType)->withResolver(
            fn (array $page) => $this->currentSite->get()->getLanguageById($page['sys_language_uid'] ?? 0)
        );
    }
}
