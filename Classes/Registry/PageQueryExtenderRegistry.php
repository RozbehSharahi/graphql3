<?php

namespace RozbehSharahi\Graphql3\Registry;

use RozbehSharahi\Graphql3\Domain\Model\QueryExtender;
use TYPO3\CMS\Core\Database\ConnectionPool;

class PageQueryExtenderRegistry
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
}
