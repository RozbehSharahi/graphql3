<?php

namespace RozbehSharahi\Graphql3TestExtension;

use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNodeCollection;
use RozbehSharahi\Graphql3\Type\PageTypeExtenderInterface;

class Md5PageTypeExtender implements PageTypeExtenderInterface
{
    public function extendNodes(GraphqlNodeCollection $nodes): GraphqlNodeCollection
    {
        return $nodes->add(
            GraphqlNode::create('md5')->withResolver(fn ($page) => md5(json_encode($page, JSON_THROW_ON_ERROR)))
        );
    }
}
