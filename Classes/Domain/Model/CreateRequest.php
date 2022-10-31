<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Domain\Model;

use RozbehSharahi\Graphql3\Domain\Model\Tca\TableConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class CreateRequest
{
    /**
     * @param array<string|mixed> $data
     */
    public static function create(TableConfiguration|string $table, array $data): self
    {
        if (is_string($table)) {
            $table = TableConfiguration::create($table);
        }

        return GeneralUtility::makeInstance(self::class, $table, $data);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function __construct(protected TableConfiguration $table, protected array $data)
    {
    }

    public function getTable(): TableConfiguration
    {
        return $this->table;
    }

    /**
     * @return array<string, mixed>
     */
    public function getData(): array
    {
        return $this->data;
    }
}
