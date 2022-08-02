<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Domain\Model;

/**
 * Page object.
 *
 * This class is mainly used to pass a page array to voters.
 *
 * By this it is easier to determine the type of the record on voters and it also provides some helpful getters
 * in order to do permission checking.
 *
 * However, you can also retrieve the page array by Page::getData().
 */
class Record
{
    /**
     * @param array<string, mixed> $data
     */
    public function __construct(protected string $table, protected array $data)
    {
    }

    public function getTable(): string
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

    public function isShowAtAnyLogin(): bool
    {
        return in_array('-2', $this->getFrontendGroups(), false);
    }

    /**
     * @return array<int, string>
     */
    public function getFrontendGroups(): array
    {
        $feGroupField = $GLOBALS['TCA'][$this->table]['ctrl']['enablecolumns']['fe_group'] ?? null;

        if (null === $feGroupField) {
            return [];
        }

        $groupList = $this->data[$feGroupField] ?? null;

        if (empty($groupList)) {
            return [];
        }

        return explode(',', $groupList);
    }
}
