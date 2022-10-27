<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Builder\FieldCreator;

use GraphQL\Type\Definition\Type;
use RozbehSharahi\Graphql3\Builder\Type\FileReferenceTypeBuilder;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;
use RozbehSharahi\Graphql3\Domain\Model\Record;
use RozbehSharahi\Graphql3\Domain\Model\Tca\ColumnConfiguration;
use TYPO3\CMS\Core\Resource\FileRepository;

class FileReferenceFieldCreator implements FieldCreatorInterface
{
    public function __construct(
        protected FileReferenceTypeBuilder $fileReferenceTypeBuilder,
        protected FileRepository $fileRepository
    ) {
    }

    public static function getPriority(): int
    {
        return 0;
    }

    public function supportsField(ColumnConfiguration $column): bool
    {
        return $column->isFile();
    }

    public function createField(ColumnConfiguration $column): GraphqlNode
    {
        return GraphqlNode::create()
            ->withName($column->getGraphqlName())
            ->withType(Type::listOf($this->fileReferenceTypeBuilder->build()))
            ->withResolver(fn (Record $record) => $this
                ->fileRepository
                ->findByRelation($record->getTable()->getName(), $column->getName(), $record->getUid()))
        ;
    }
}
