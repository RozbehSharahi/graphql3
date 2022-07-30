<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Node;

use RozbehSharahi\Graphql3\Domain\Model\Context;

interface PageNodePostFetchFilterExtenderInterface
{
    public function supportsContext(Context $context): bool;

    public function postFetchFilter(array $pages, array $arguments): array;
}
