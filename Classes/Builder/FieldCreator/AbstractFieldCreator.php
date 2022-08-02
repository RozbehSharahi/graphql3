<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Builder\FieldCreator;

use RozbehSharahi\Graphql3\Converter\CaseConverter;
use TYPO3\CMS\Core\Utility\GeneralUtility;

abstract class AbstractFieldCreator
{
    protected function getConverter(): CaseConverter
    {
        return GeneralUtility::makeInstance(CaseConverter::class);
    }

    /**
     * @return array<string, mixed>
     */
    protected function getFieldConfiguration(string $tableName, string $columnName): array
    {
        $configuration = $GLOBALS['TCA'][$tableName]['columns'][$columnName]['config'] ?? [];

        // Provide a bit default array element to avoid undefined checks
        $configuration['type'] ??= null;
        $configuration['graphql3'] ??= [];
        $configuration['graphql3']['name'] ??= null;
        $configuration['format'] ??= null;

        return $configuration;
    }

    protected function getName(string $tableName, string $columnName): string
    {
        $configuration = $this->getFieldConfiguration($tableName, $columnName);

        return $configuration['graphql3']['name'] ?? $this->getConverter()->toCamel($columnName);
    }

    protected function getLanguageParentColumnName(string $tableName): ?string
    {
        return $GLOBALS['TCA'][$tableName]['ctrl']['transOrigPointerField'] ?? null;
    }
}
