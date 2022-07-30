<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Node;

use RozbehSharahi\Graphql3\Domain\Model\Context;

interface PageNodePostFetchFilterExtenderInterface
{
    public function supportsContext(Context $context): bool;

    /**
     * @param array<int, array<string,mixed>> $pages
     * @param array<string, mixed>            $arguments
     *
     * @return array<int, array<string,mixed>>
     */
    public function postFetchFilter(array $pages, array $arguments): array;
}
