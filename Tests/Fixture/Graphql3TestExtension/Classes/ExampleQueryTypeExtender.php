<?php

declare(strict_types=1);

/** @noinspection PhpIllegalPsrClassPathInspection */

namespace RozbehSharahi\Graphql3TestExtension;

use RozbehSharahi\Graphql3\Builder\RecordListNodeBuilder;
use RozbehSharahi\Graphql3\Builder\RecordNodeBuilder;
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
            ->add($this->recordListNodeBuilder->for('sys_news')->build()->withName('sysNewsItems'))
            ->add($this->recordNodeBuilder->for('sys_news')->build()->withName('sysNewsItem'))
        ;
    }
}
