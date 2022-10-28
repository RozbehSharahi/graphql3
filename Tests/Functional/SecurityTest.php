<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Tests\Functional;

use PHPUnit\Framework\TestCase;
use RozbehSharahi\Graphql3\Security\JwtManager;
use RozbehSharahi\Graphql3\Tests\Fixture\Security\Jwt;
use RozbehSharahi\Graphql3\Tests\Functional\Core\FunctionalTrait;

class SecurityTest extends TestCase
{
    use FunctionalTrait;

    /**
     * @dataProvider canCreateJwtTokenDataProvider
     */
    public function testCanCreateJwtToken(string $algorithm, string $privateKey, ?string $publicKey): void
    {
        $scope = $this->getFunctionalScopeBuilder()->build();

        $jwtManager = $scope->get(JwtManager::class)
            ->withAlgorithm($algorithm)
            ->withPrivateKey($privateKey)
        ;

        if ($publicKey) {
            $jwtManager = $jwtManager->withPublicKey($publicKey);
        }

        $token = $jwtManager->create(new \DateTimeImmutable('now + 3600 seconds'), [
            'admin' => true,
            'object' => [
                'someProp' => 1234,
            ],
        ]);

        self::assertFalse($jwtManager->isExpired($token));

        [$header, $payload, $signature] = explode('.', $token);

        self::assertNotEmpty($header);
        self::assertNotEmpty($payload);
        self::assertNotEmpty($signature);

        self::assertTrue($jwtManager->verify($token));

        $tokenData = $jwtManager->decode($token);

        self::assertTrue($tokenData['admin']);
        self::assertEquals(1234, $tokenData['object']['someProp']);
        self::assertNotEmpty($tokenData['exp']);
        self::assertNotEmpty($tokenData['iat']);
    }

    /**
     * @return array<string, mixed>
     */
    public function canCreateJwtTokenDataProvider(): array
    {
        return [
            'rs256' => [
                JwtManager::ALGORITHM_RS256,
                Jwt::TEST_PRIVATE_KEY,
                Jwt::TEST_PUBLIC_KEY,
            ],
            'hs256' => [
                JwtManager::ALGORITHM_HS256,
                'some-password',
                'some-password',
            ],
            'hs256 without public key (falls back to private-key)' => [
                JwtManager::ALGORITHM_HS256,
                'some-password',
                null,
            ],
        ];
    }
}
