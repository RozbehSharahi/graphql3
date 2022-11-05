<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Domain\Model;

use RozbehSharahi\Graphql3\Domain\Model\Tca\ColumnConfiguration;
use RozbehSharahi\Graphql3\Domain\Model\Tca\TableConfiguration;
use TYPO3\CMS\Core\Service\FlexFormService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class FlexFormRecord extends Record
{
    public static function create(TableConfiguration|string $table, array $row): self
    {
        if (is_string($table)) {
            $table = TableConfiguration::create($table);
        }

        return GeneralUtility::makeInstance(self::class, $table, $row);
    }

    public static function createFromRecordAndColumn(Record $record, ColumnConfiguration $column): self
    {
        if (empty($record->get($column))) {
            return new self($record->getTable(), $record->toArray());
        }

        $flexFormService = GeneralUtility::makeInstance(FlexFormService::class);
        $data = $flexFormService->convertFlexFormContentToArray($record->get($column));

        return GeneralUtility::makeInstance(self::class, $record->getTable(), $record->toArray());
    }
}
