<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Builder\Type\Extender;

use RozbehSharahi\Graphql3\Builder\Type\RecordListTypeBuilder;
use RozbehSharahi\Graphql3\Builder\Type\RecordTypeBuilderExtenderInterface;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNodeCollection;
use RozbehSharahi\Graphql3\Domain\Model\ListRequest;
use RozbehSharahi\Graphql3\Domain\Model\Record;
use RozbehSharahi\Graphql3\Domain\Model\Tca\TableConfiguration;
use RozbehSharahi\Graphql3\Service\DataService;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;

class PageChildrenRecordTypeBuilderExtender implements RecordTypeBuilderExtenderInterface
{
    public function __construct(
        protected RecordListTypeBuilder $recordListTypeBuilder,
        protected DataService $dataService,
        protected ConnectionPool $connectionPool
    ) {
    }

    public function supportsTable(TableConfiguration $tableConfiguration): bool
    {
        return 'pages' === $tableConfiguration->getName();
    }

    public function extendNodes(
        TableConfiguration $tableConfiguration,
        GraphqlNodeCollection $nodes
    ): GraphqlNodeCollection {
        return $nodes->add(
            GraphqlNode::create()
                ->withName('children')
                ->withType($this->recordListTypeBuilder->for('pages')->build())
                ->withResolver(function ($row, array $args) use ($tableConfiguration) {
                    $record = Record::create($tableConfiguration->getName(), $row);

                    $record->assertRootPageLanguageIntegrity();

                    $childrenPid = 0 === $record->getLanguageUid()
                        ? $record->getUid()
                        : $record->getLanguageParentUid();

                    return ListRequest::create($args)
                        ->withLanguageFromRecord($record)
                        ->withQueryModifier(fn (QueryBuilder $q) => $q->andWhere($q->expr()->eq('pid', $childrenPid)))
                    ;
                })
        );
    }
}
