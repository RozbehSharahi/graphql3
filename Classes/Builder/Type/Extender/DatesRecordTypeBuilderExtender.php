<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Builder\Type\Extender;

use GraphQL\Type\Definition\Type;
use RozbehSharahi\Graphql3\Builder\Type\RecordTypeBuilderExtenderInterface;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlArgument;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlArgumentCollection;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNodeCollection;
use RozbehSharahi\Graphql3\Domain\Model\Tca\TableConfiguration;

class DatesRecordTypeBuilderExtender implements RecordTypeBuilderExtenderInterface
{
    public function supportsTable(TableConfiguration $tableConfiguration): bool
    {
        return true;
    }

    public function extendNodes(
        TableConfiguration $tableConfiguration,
        GraphqlNodeCollection $nodes
    ): GraphqlNodeCollection {
        $arguments = GraphqlArgumentCollection::create([
            GraphqlArgument::create('format')->withType(Type::nonNull(Type::string()))->withDefaultValue('Y-m-d H:i'),
        ]);

        if ($tableConfiguration->hasCreatedAt()) {
            $nodes = $nodes->add(
                GraphqlNode::create()
                    ->withName('createdAt')
                    ->withArguments($arguments)
                    ->withResolver(fn ($record, $args) => date($args['format'], $record['crdate']))
            );
        }

        if ($tableConfiguration->hasUpdatedAt()) {
            $nodes = $nodes->add(
                GraphqlNode::create()
                    ->withName('updatedAt')
                    ->withArguments($arguments)
                    ->withResolver(fn ($record, $args) => date($args['format'], $record['tstamp']))
            );
        }

        return $nodes;
    }
}
