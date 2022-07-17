<?php

namespace RozbehSharahi\Graphql3\Registry;

use RozbehSharahi\Graphql3\Domain\Model\QueryExtender;
use RozbehSharahi\Graphql3\Setup\SetupInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;

class PageQueryExtenderRegistry implements SetupInterface
{
    /**
     * @var QueryExtender[]
     */
    protected array $extenders = [];

    public function __construct(protected ConnectionPool $connectionPool)
    {
    }

    public function register(QueryExtender $extender): self
    {
        $this->extenders[$extender->getName()] = $extender;

        return $this;
    }

    public function getExtenders(): array
    {
        return $this->extenders;
    }

    public function setup(): void
    {
        $this->register(
            QueryExtender::create('uid-extender')
                ->withClosure(
                    static fn (QueryBuilder $query, array $arguments = []) => $query
                        ->andWhere($query->expr()->eq('uid', $query->createNamedParameter($arguments['uid'])))
                )
        );
    }
}
