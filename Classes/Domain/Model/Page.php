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
class Page
{
    /**
     * @param array<string, mixed> $data
     */
    public function __construct(protected array $data)
    {
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
        $groupList = $this->data['fe_group'] ?? null;

        if (empty($groupList)) {
            return [];
        }

        return explode(',', $groupList);
    }
}
