<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Builder;

use GraphQL\Type\Definition\Type;
use RozbehSharahi\Graphql3\Domain\Model\FlexFormRecord;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlArgumentCollection;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;
use RozbehSharahi\Graphql3\Domain\Model\Record;
use RozbehSharahi\Graphql3\Domain\Model\Tca\ColumnConfiguration;
use RozbehSharahi\Graphql3\Exception\InternalErrorException;

class FlexFormNodeBuilder implements NodeBuilderInterface
{
    public const ERROR_NO_COLUMN_DEFINED = 'Attempt to use flex-form-builder without "column" defined. Did you forget to call ->for(...)?';

    protected ColumnConfiguration $column;

    public function __construct(protected FlexFormTypeBuilder $flexFormTypeBuilder)
    {
    }

    public function getColumn(): ColumnConfiguration
    {
        return $this->column;
    }

    public function for(ColumnConfiguration $column): self
    {
        $clone = clone $this;
        $clone->column = $column;

        return $clone;
    }

    public function build(): GraphqlNode
    {
        $this->assertConfigured();

        return GraphqlNode::create();
    }

    public function buildArguments(): GraphqlArgumentCollection
    {
        $this->assertConfigured();

        return GraphqlArgumentCollection::create();
    }

    public function buildType(): Type
    {
        $this->assertConfigured();

        return $this->flexFormTypeBuilder->for($this->column)->build();
    }

    public function buildResolver(): \Closure
    {
        $this->assertConfigured();

        return function (Record $record) {
            return FlexFormRecord::createFromRecordAndColumn($record, $this->column);
        };
    }

    protected function assertConfigured(): self
    {
        if (empty($this->column)) {
            throw new InternalErrorException(self::ERROR_NO_COLUMN_DEFINED);
        }

        return $this;
    }
}
