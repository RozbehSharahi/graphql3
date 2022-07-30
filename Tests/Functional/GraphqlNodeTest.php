<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Tests\Functional;

use GraphQL\Type\Definition\IntType;
use GraphQL\Type\Definition\NonNull;
use GraphQL\Type\Definition\Type;
use PHPUnit\Framework\TestCase;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlArgument;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlArgumentCollection;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;

class GraphqlNodeTest extends TestCase
{
    public function testCanCreateNodeForGraphql(): void
    {
        $node = GraphqlNode::create('will-be-replaced')
            ->withName('myNodeName')
            ->withArguments(GraphqlArgumentCollection::create([
                GraphqlArgument::create('myArgument')->withType(Type::int()),
            ]))
            ->withResolver(fn () => 'Hey')
            ->withType(Type::nonNull(Type::string()))
        ;

        $nodeArray = $node->toArray();

        self::assertInstanceOf(NonNull::class, $nodeArray['type']);
        self::assertSame('Hey', $nodeArray['resolve']());
        self::assertSame('myArgument', $nodeArray['args']['myArgument']['name']);
        self::assertInstanceOf(IntType::class, $nodeArray['args']['myArgument']['type']);
    }
}
