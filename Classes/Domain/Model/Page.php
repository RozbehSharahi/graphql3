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
    public function __construct(protected array $data)
    {
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function isShowAtAnyLogin(): bool
    {
        return in_array('-2', $this->getFrontendGroups(), false);
    }

    public function getFrontendGroups(): array
    {
        $groupList = $this->data['fe_group'] ?? null;

        if (empty($groupList)) {
            return [];
        }

        return explode(',', $groupList);
    }
}
