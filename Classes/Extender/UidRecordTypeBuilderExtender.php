<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Extender;

use GraphQL\Type\Definition\Type;
use RozbehSharahi\Graphql3\Builder\RecordTypeBuilderExtenderInterface;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNodeCollection;
use RozbehSharahi\Graphql3\Domain\Model\Record;
use RozbehSharahi\Graphql3\Domain\Model\Tca\TableConfiguration;

class UidRecordTypeBuilderExtender implements RecordTypeBuilderExtenderInterface
{
    public function supportsTable(TableConfiguration $table): bool
    {
        return true;
    }

    public function extendNodes(
        TableConfiguration $table,
        GraphqlNodeCollection $nodes
    ): GraphqlNodeCollection {
        return $nodes->add(
            GraphqlNode::create()
                ->withName('uid')
                ->withType(Type::int())
                ->withResolver(fn (Record $record) => $record->getUid())
        );
    }
}
