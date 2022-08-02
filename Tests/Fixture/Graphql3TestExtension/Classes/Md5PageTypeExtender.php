<?php

/** @noinspection PhpIllegalPsrClassPathInspection */

declare(strict_types=1);

namespace RozbehSharahi\Graphql3TestExtension;

use RozbehSharahi\Graphql3\Builder\Type\RecordTypeBuilderExtenderInterface;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNodeCollection;

class Md5PageTypeExtender implements RecordTypeBuilderExtenderInterface
{
    public function supportsTable(string $table): bool
    {
        return 'pages' === $table;
    }

    public function extendNodes(GraphqlNodeCollection $nodes): GraphqlNodeCollection
    {
        return $nodes->add(
            GraphqlNode::create('md5')->withResolver(fn ($page) => md5(json_encode($page, JSON_THROW_ON_ERROR)))
        );
    }
}
