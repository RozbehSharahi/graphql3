<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Tests\Functional;

use PHPUnit\Framework\TestCase;
use RozbehSharahi\Graphql3\Tests\Functional\Core\FunctionalTrait;

class TestTest extends TestCase
{
    use FunctionalTrait;

    public function testCanCreateFreshDatabase(): void
    {
        $this
            ->getFunctionalScopeBuilder()
            ->withFreshDatabase(true)
            ->withAutoCreateHomepage(false)
            ->build()
        ;

        self::assertEquals(true, true);
    }
}
