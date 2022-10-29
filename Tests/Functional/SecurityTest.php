<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Tests\Functional;

use PHPUnit\Framework\TestCase;
use RozbehSharahi\Graphql3\Security\JwtManager;
use RozbehSharahi\Graphql3\Tests\Fixture\Security\JwtKeys;
use RozbehSharahi\Graphql3\Tests\Functional\Core\FunctionalTrait;

class SecurityTest extends TestCase
{
    use FunctionalTrait;

    /**
     * @dataProvider canCreateJwtTokenDataProvider
     */
    public function testCanCreateJwtToken(
        string $algorithm,
        string $privateKey,
        ?string $publicKey,
        ?string $passphrase
    ): void {
        $scope = $this->getFunctionalScopeBuilder()->build();

        $jwtManager = $scope->get(JwtManager::class)
            ->withAlgorithm($algorithm)
            ->withPrivateKey($privateKey)
            ->withPassphrase($passphrase)
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

        $tokenData = $jwtManager->read($token);

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
                JwtKeys::KEY_PAIR__PRIVATE_KEY,
                JwtKeys::KEY_PAIR__PUBLIC_KEY,
                null,
            ],
            'rs256_passphrase' => [
                JwtManager::ALGORITHM_RS256,
                JwtKeys::KEY_PAIR_PASSPHRASE__PRIVATE_KEY,
                JwtKeys::KEY_PAIR_PASSPHRASE__PUBLIC_KEY,
                JwtKeys::KEY_PAIR_PASSPHRASE__PASSPHRASE,
            ],
            'hs256' => [
                JwtManager::ALGORITHM_HS256,
                'some-password',
                'some-password',
                null,
            ],
            'hs256 without public key (falls back to private-key)' => [
                JwtManager::ALGORITHM_HS256,
                'some-password',
                null,
                null,
            ],
        ];
    }
}
