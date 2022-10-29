<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Tests\Functional;

use PHPUnit\Framework\TestCase;
use RozbehSharahi\Graphql3\Domain\Model\JwtUser;
use RozbehSharahi\Graphql3\Security\JwtManager;
use RozbehSharahi\Graphql3\Tests\Functional\Core\FunctionalTrait;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\UserAspect;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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

        self::assertTrue($jwtManager->isValid($token));

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
                file_get_contents(__DIR__.'/../Fixture/KeyPairs/Rs256/private.pem'),
                file_get_contents(__DIR__.'/../Fixture/KeyPairs/Rs256/public.pem'),
                null,
            ],
            'rs256_file_path' => [
                JwtManager::ALGORITHM_RS256,
                'file://Tests/Fixture/KeyPairs/Rs256/private.pem',
                'file://Tests/Fixture/KeyPairs/Rs256/public.pem',
                null,
            ],
            'rs256_passphrase' => [
                JwtManager::ALGORITHM_RS256,
                file_get_contents(__DIR__.'/../Fixture/KeyPairs/Rs256Passphrase/private.pem'),
                file_get_contents(__DIR__.'/../Fixture/KeyPairs/Rs256Passphrase/public.pem'),
                file_get_contents(__DIR__.'/../Fixture/KeyPairs/Rs256Passphrase/passphrase.txt'),
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

    public function testCanCreateJwtTokenFromSession(): void
    {
        $userAspect = $this->createMock(UserAspect::class);
        $userAspect->method('isLoggedIn')->willReturn(true);
        $userAspect->method('get')->willReturnCallback(fn ($param) => [
            'username' => 'test-user',
            'id' => 1,
        ][$param]);
        $userAspect->method('getGroupIds')->willReturn([-1, 0, '0', '-3', 2, 4]);

        GeneralUtility::makeInstance(Context::class)
            ->setAspect('frontend.user', $userAspect)
        ;

        $user = JwtUser::createFromSession();

        self::assertEquals([JwtUser::createGroupIdRole(2), JwtUser::createGroupIdRole(4)], $user->getRoles());
    }
}
