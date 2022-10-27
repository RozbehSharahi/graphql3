<?php

declare(strict_types=1);

/** @noinspection PhpIllegalPsrClassPathInspection */

namespace RozbehSharahi\Graphql3TestExtension;

use GraphQL\Type\Definition\Type;
use RozbehSharahi\Graphql3\Builder\Node\RecordListNodeBuilder;
use RozbehSharahi\Graphql3\Builder\Node\RecordNodeBuilder;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNodeCollection;
use RozbehSharahi\Graphql3\Type\QueryTypeExtenderInterface;

class ExampleQueryTypeExtender implements QueryTypeExtenderInterface
{
    public function __construct(
        protected RecordNodeBuilder $recordNodeBuilder,
        protected RecordListNodeBuilder $recordListNodeBuilder
    ) {
    }

    public function extend(GraphqlNodeCollection $nodes): GraphqlNodeCollection
    {
        return $nodes
            ->add($this->recordListNodeBuilder->for('sys_log')->build())
            ->add($this->recordNodeBuilder->for('sys_log')->build())
            ->add(GraphqlNode::create('someNode')->withType(Type::string())->withResolver(fn () => 'Hello World'))
        ;
    }
}
