<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\FlexFormFieldCreator;

use GraphQL\Type\Definition\Type;
use RozbehSharahi\Graphql3\Builder\FileReferenceTypeBuilder;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;
use RozbehSharahi\Graphql3\Domain\Model\Record;
use RozbehSharahi\Graphql3\Domain\Model\Tca\ColumnConfiguration;
use RozbehSharahi\Graphql3\Exception\InternalErrorException;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Resource\FileRepository;

class FileReferenceFlexFormFieldCreator implements FlexFormFieldCreatorInterface
{
    public const ERROR_WRONG_CONFIGURATION = 'In order to use %s as a graphql node for fetching file-references it must have a foreign_match_fields.fieldname assigned.';

    public function __construct(
        protected FileReferenceTypeBuilder $fileReferenceTypeBuilder,
        protected FileRepository $fileRepository,
        protected ConnectionPool $connectionPool
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
            ->withResolver(fn (Record $record) => $this->getFileReferences($record, $column))
        ;
    }

    /**
     * @return array<FileReference>
     */
    protected function getFileReferences(Record $record, ColumnConfiguration $column): array
    {
        $fieldName = $column->getForeignMatchFields()['fieldname'] ?? null;

        if (!$fieldName) {
            throw new InternalErrorException(sprintf(self::ERROR_WRONG_CONFIGURATION, $column->getFullName()));
        }

        $query = $this->connectionPool->getQueryBuilderForTable('sys_file_reference');

        $query = $query
            ->select('uid')
            ->from('sys_file_reference')
            ->where(
                $query
                    ->expr()->eq('uid_foreign', $query->createNamedParameter($record->getUid(), \PDO::PARAM_INT)),
                $query
                    ->expr()->eq('tablenames', $query->createNamedParameter($record->getTable()->getName())),
                $query
                    ->expr()->eq('fieldname', $query->createNamedParameter($fieldName))
            )
            ->orderBy('sorting_foreign')
        ;

        try {
            $uids = array_map(static fn ($v) => $v['uid'], $query->executeQuery()->fetchAllAssociative());

            return array_map(fn ($v) => $this->fileRepository->findFileReferenceByUid($v), $uids);
        } catch (\Throwable $e) {
            throw new InternalErrorException('Error on fetching file-references for '.$column->getFullName().': '.$e->getMessage());
        }
    }
}
