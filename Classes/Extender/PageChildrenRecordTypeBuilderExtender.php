<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Extender;

use RozbehSharahi\Graphql3\Builder\RecordListNodeBuilder;
use RozbehSharahi\Graphql3\Builder\RecordTypeBuilderExtenderInterface;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNodeCollection;
use RozbehSharahi\Graphql3\Domain\Model\ListRequest;
use RozbehSharahi\Graphql3\Domain\Model\Record;
use RozbehSharahi\Graphql3\Domain\Model\Tca\TableConfiguration;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;

class PageChildrenRecordTypeBuilderExtender implements RecordTypeBuilderExtenderInterface
{
    public function __construct(
        protected RecordListNodeBuilder $recordListNodeBuilder,
        protected ConnectionPool $connectionPool
    ) {
    }

    public function supportsTable(TableConfiguration $table): bool
    {
        return 'pages' === $table->getName();
    }

    public function extendNodes(
        TableConfiguration $table,
        GraphqlNodeCollection $nodes
    ): GraphqlNodeCollection {
        $recordListNodeBuilder = $this->recordListNodeBuilder->for('pages');

        return $nodes->add(
            GraphqlNode::create()
                ->withName('children')
                ->withType($recordListNodeBuilder->buildType())
                ->withArguments($recordListNodeBuilder->buildArguments()->remove('language'))
                ->withResolver(function (Record $record, array $args) {
                    $record->assertRootPageLanguageIntegrity();

                    $childrenPid = 0 === $record->getLanguageUid()
                        ? $record->getUid()
                        : $record->getLanguageParentUid();

                    return ListRequest::create($args)
                        ->withLanguageFromRecord($record)
                        ->withQueryModifier(
                            fn (QueryBuilder $q) => $q->andWhere($q->expr()->eq('pid', $childrenPid))
                        )
                    ;
                })
        );
    }
}
