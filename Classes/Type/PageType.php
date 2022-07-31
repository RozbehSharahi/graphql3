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
use RozbehSharahi\Graphql3\Node\Nested\NestedLanguageNode;
use RozbehSharahi\Graphql3\Node\Nested\NestedNodeRegistry;
use RozbehSharahi\Graphql3\Node\Nested\NestedPageNode;

class PageType extends ObjectType
{
    /**
     * @param iterable<PageTypeExtenderInterface> $extenders
     */
    public function __construct(
        protected NestedNodeRegistry $nestedNodeRegistry,
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
                ->add($this->createStringNode('subtitle', 'subtitle'))
                ->add($this->createStringNode('keywords', 'keywords'))
                ->add($this->createStringNode('navigationTitle', 'nav_title'))
                ->add($this->createBoolNode('navigationHide', 'nav_hide'))
                ->add($this->createIntNode('type', 'doktype'))
                ->add($this->createIntNode('shortcut', 'shortcut'))
                ->add($this->createIntNode('shortcutMode', 'shortcut_mode'))
                ->add($this->createIntNode('layout', 'layout'))
                ->add($this->createIntNode('target', 'target'))
                ->add($this->createDateTimeNode('updatedAt', 'tstamp'))
                ->add($this->createDateTimeNode('createdAt', 'crdate'))
                ->add($this->createBoolNode('deleted', 'deleted'))
                ->add($this->createBoolNode('hidden', 'hidden'))
                ->add($this->createDateTimeNode('startTime', 'starttime'))
                ->add($this->createDateTimeNode('endTime', 'endtime'))
                ->add($this->createIntNode('sorting', 'sorting'))
                ->add($this->createPageNode('parent', 'pid'))
                ->add($this->createPageNode('languageParent', 'l10n_parent'))
                ->add($this->createPageChildrenNode('children'))
                ->add($this->createMediaNode())
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
        return $this
            ->nestedNodeRegistry
            ->get(NestedPageNode::class)
            ->withName($name)
            ->withIdResolver(fn ($page) => $page[$property])
            ->getGraphqlNode()
        ;
    }

    protected function createPageChildrenNode(string $name): GraphqlNode
    {
        return $this
            ->nestedNodeRegistry
            ->get(NestedPageNode::class)
            ->withName($name)
            ->withIdResolver(fn ($page) => $page['uid'])
            ->getChildrenGraphqlNode()
        ;
    }

    protected function createLanguageNode(): GraphqlNode
    {
        return $this
            ->nestedNodeRegistry
            ->get(NestedLanguageNode::class)
            ->withName('language')
            ->withIdResolver(fn ($page) => $page['sys_language_uid'] ?? 0)
            ->getGraphqlNode()
        ;
    }

    protected function createMediaNode(): GraphqlNode
    {
        return GraphqlNode::create('media')->withType(Type::string())->withResolver(fn ($v) => $v['media']);
    }
}
