<?php

namespace RozbehSharahi\Graphql3\Tests\Functional;

use GraphQL\Type\Definition\FloatType;
use GraphQL\Type\Definition\StringType;
use GraphQL\Type\Definition\Type;
use PHPUnit\Framework\TestCase;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlArgument;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlArgumentCollection;

class GraphqlArgumentCollectionTest extends TestCase
{
    public function testCanCreateNodeForGraphql(): void
    {
        $collection = GraphqlArgumentCollection::create();

        $collection = $collection
            ->add(GraphqlArgument::create('myArgument1')->withType(Type::string()))
            ->add(GraphqlArgument::create('myArgument2')->withType(Type::boolean()))
            ->add(GraphqlArgument::create('myArgument3')->withType(Type::int()));

        // Remove node 2
        $collection = $collection->remove('myArgument2');

        // Overriding node 3
        $collection = $collection->add(GraphqlArgument::create('myArgument3')->withType(Type::float()));

        self::assertSame(2, $collection->getLength());
        self::assertInstanceOf(StringType::class, $collection->toArray()['myArgument1']['type']);
        self::assertInstanceOf(FloatType::class, $collection->toArray()['myArgument3']['type']);
    }
}
