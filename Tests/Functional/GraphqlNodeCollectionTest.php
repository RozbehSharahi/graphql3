<?php

namespace RozbehSharahi\Graphql3\Tests\Functional;

use GraphQL\Type\Definition\FloatType;
use GraphQL\Type\Definition\StringType;
use GraphQL\Type\Definition\Type;
use PHPUnit\Framework\TestCase;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNodeCollection;

class GraphqlNodeCollectionTest extends TestCase
{
    public function testCanCreateNodeForGraphql(): void
    {
        $collection = GraphqlNodeCollection::create();

        $collection = $collection
            ->add(GraphqlNode::create('myNode1')->withType(Type::string()))
            ->add(GraphqlNode::create('myNode2')->withType(Type::boolean()))
            ->add(GraphqlNode::create('myNode3')->withType(Type::int()));

        // Remove node 2
        $collection = $collection->remove('myNode2');

        // Overriding node 3
        $collection = $collection->add(GraphqlNode::create('myNode3')->withType(Type::float()));

        self::assertSame(2, $collection->getLength());
        self::assertInstanceOf(StringType::class, $collection->toArray()['myNode1']['type']);
        self::assertInstanceOf(FloatType::class, $collection->toArray()['myNode3']['type']);
    }
}
