<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\FieldCreator;

use RozbehSharahi\Graphql3\Builder\FlexFormNodeBuilder;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;
use RozbehSharahi\Graphql3\Domain\Model\Tca\ColumnConfiguration;

class FlexFormFieldCreator implements FieldCreatorInterface
{
    public static function getPriority(): int
    {
        return 0;
    }

    public function __construct(protected FlexFormNodeBuilder $flexFormNodeBuilder)
    {
    }

    public function supportsField(ColumnConfiguration $column): bool
    {
        return $column->isSimpleFlex();
    }

    public function createField(ColumnConfiguration $column): GraphqlNode
    {
        $nodeBuilder = $this->flexFormNodeBuilder->for($column);

        // @todo handle flex inheritance

        return GraphqlNode::create()
            ->withName('data')
            ->withType($nodeBuilder->buildType())
            ->withArguments($nodeBuilder->buildArguments())
            ->withResolver($nodeBuilder->buildResolver())
        ;
    }
}
