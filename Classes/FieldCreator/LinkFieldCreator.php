<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\FieldCreator;

use GraphQL\Type\Definition\Type;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlArgument;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlArgumentCollection;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;
use RozbehSharahi\Graphql3\Domain\Model\Record;
use RozbehSharahi\Graphql3\Domain\Model\Tca\ColumnConfiguration;
use RozbehSharahi\Graphql3\Exception\InternalErrorException;
use RozbehSharahi\Graphql3\Service\TypolinkService;

class LinkFieldCreator implements FieldCreatorInterface
{
    public static function getPriority(): int
    {
        return 0;
    }

    public function __construct(protected TypolinkService $typolinkService)
    {
    }

    public function supportsField(ColumnConfiguration $column): bool
    {
        return $column->isLink();
    }

    public function createField(ColumnConfiguration $column): GraphqlNode
    {
        return GraphqlNode::create()
            ->withName($column->getGraphqlName())
            ->withArguments(GraphqlArgumentCollection::create([
                GraphqlArgument::create()
                    ->withName('parse')
                    ->withType(Type::nonNull(Type::boolean()))
                    ->withDefaultValue(true),
            ]))
            ->withResolver(function (Record $record, array $args) use ($column) {
                $typolink = $record->get($column);

                if (empty($typolink)) {
                    return null;
                }

                if (!is_string($typolink)) {
                    throw new InternalErrorException('Typolink input is not a string');
                }

                return $args['parse'] ? $this->typolinkService->parse($typolink) : $typolink;
            })
        ;
    }
}
