<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Builder\Type\Extender;

use GraphQL\Type\Definition\Type;
use RozbehSharahi\Graphql3\Builder\Type\RecordTypeBuilderExtenderInterface;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNodeCollection;

class DeletedRecordTypeBuilderExtender implements RecordTypeBuilderExtenderInterface
{
    public function supportsTable(string $table): bool
    {
        return true;
    }

    public function extendNodes(GraphqlNodeCollection $nodes): GraphqlNodeCollection
    {
        return $nodes->add(
            GraphqlNode::create()
                ->withName('deleted')
                ->withType(Type::boolean())
                ->withResolver(fn (array $record) => !empty($record['deleted']))
        );
    }
}
