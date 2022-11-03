<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Extender;

use RozbehSharahi\Graphql3\Domain\Model\ListRequest;
use RozbehSharahi\Graphql3\Domain\Model\Tca\TableConfiguration;
use RozbehSharahi\Graphql3\Resolver\RecordListResolverExtenderInterface;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;

class DoktypeFilterRecordListResolverExtender implements RecordListResolverExtenderInterface
{
    public function supports(TableConfiguration $table): bool
    {
        return 'pages' === $table->getName();
    }

    public function extend(TableConfiguration $table, ListRequest $request, QueryBuilder $query): QueryBuilder
    {
        if ($request->hasArgument('allDoktypes') && $request->getArgument('allDoktypes')) {
            return $query;
        }

        return $query->andWhere(
            $query->expr()->in('doktype', [
                '0',
                PageRepository::DOKTYPE_DEFAULT,
                PageRepository::DOKTYPE_LINK,
                PageRepository::DOKTYPE_SHORTCUT,
                PageRepository::DOKTYPE_SPACER,
            ])
        );
    }
}
