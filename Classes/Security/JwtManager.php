<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Security;

use DateTime;
use DateTimeInterface;
use DomainException;
use Firebase\JWT\BeforeValidException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\SignatureInvalidException;
use RozbehSharahi\Graphql3\Exception\InternalErrorException;
use RozbehSharahi\Graphql3\Exception\ShouldNotHappenException;

class JwtManager
{
    public const SUPPORTED_ALGORITHMS = [self::ALGORITHM_RS256, self::ALGORITHM_HS256];

    public const ALGORITHM_RS256 = 'RS256';

    public const DEFAULT_ALGORITHM = 'RS256';

    public const ALGORITHM_HS256 = 'HS256';

    protected string $algorithm = self::DEFAULT_ALGORITHM;

    protected string $privateKey;

    protected string $publicKey;

    public function getAlgorithm(): string
    {
        return $this->algorithm;
    }

    public function withAlgorithm(string $algorithm): self
    {
        $clone = clone $this;

        if (!in_array($algorithm, self::SUPPORTED_ALGORITHMS)) {
            throw new InternalErrorException('Unsupported algorithm for jwt handling given: '.$algorithm);
        }

        $clone->algorithm = $algorithm;

        return $clone;
    }

    public function getPrivateKey(): string
    {
        return $this->privateKey;
    }

    public function withPrivateKey(string $privateKey): self
    {
        $clone = clone $this;
        $clone->privateKey = $privateKey;

        return $clone;
    }

    public function getPublicKey(): string
    {
        return $this->publicKey;
    }

    public function withPublicKey(string $publicKey): self
    {
        $clone = clone $this;
        $clone->publicKey = $publicKey;

        return $clone;
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function create(DateTimeInterface $dateTime, array $payload): string
    {
        $this->assertPrivateKey();

        $payload['exp'] = $dateTime->getTimestamp();
        $payload['iat'] = (new DateTime())->getTimestamp();

        // @todo passphrase

        if (self::ALGORITHM_RS256 === $this->algorithm) {
            return JWT::encode($payload, $this->privateKey, $this->algorithm);
        }

        if (self::ALGORITHM_HS256 === $this->algorithm) {
            return JWT::encode($payload, $this->privateKey, $this->algorithm);
        }

        throw new ShouldNotHappenException('Algorithm was given, but not handled.', ['alg' => $this->algorithm]);
    }

    public function verify(string $token): bool
    {
        try {
            $this->decode($token);
        } catch (DomainException|\UnexpectedValueException|SignatureInvalidException|BeforeValidException|ExpiredException) {
            return false;
        } catch (\Throwable $e) {
            throw new InternalErrorException('Unexpected issue on decoding jwt token: '.$e->getMessage());
        }

        return true;
    }

    public function isExpired(string $token): bool
    {
        try {
            $this->decode($token);
        } catch (ExpiredException) {
            return true;
        } catch (\Throwable) {
            return false;
        }

        return false;
    }

    /**
     * @return array<string, mixed>
     */
    public function decode(string $token): array
    {
        if (empty($this->publicKey)) {
            $this->assertPrivateKey();
        }

        $data = JWT::decode($token, new Key($this->publicKey ?? $this->privateKey, $this->algorithm));

        /* @noinspection JsonEncodingApiUsageInspection */
        return json_decode(json_encode($data), true);
    }

    public function assertPrivateKey(): self
    {
        if (empty($this->privateKey)) {
            throw new InternalErrorException('Private key was not set');
        }

        return $this;
    }

}
