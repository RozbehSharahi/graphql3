<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Security;

use Firebase\JWT\BeforeValidException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\SignatureInvalidException;
use RozbehSharahi\Graphql3\Exception\InternalErrorException;
use RozbehSharahi\Graphql3\Exception\ShouldNotHappenException;
use TYPO3\CMS\Core\Core\Environment;

class JwtManager
{
    public const SUPPORTED_ALGORITHMS = [self::ALGORITHM_RS256, self::ALGORITHM_HS256];

    public const ALGORITHM_RS256 = 'RS256';

    public const DEFAULT_ALGORITHM = 'RS256';

    public const ALGORITHM_HS256 = 'HS256';

    public const ENV_VAR_ALGORITHM = 'GRAPHQL3_JWT_ALGORITHM';

    public const ENV_VAR_PRIVATE_KEY = 'GRAPHQL3_JWT_PRIVATE_KEY';

    public const ENV_VAR_PUBLIC_KEY = 'GRAPHQL3_JWT_PUBLIC_KEY';

    public const ENV_VAR_PASSPHRASE = 'GRAPHQL3_JWT_PASSPHRASE';

    public const FILE_REFERENCE_PREFIX = 'file://';

    protected string $algorithm = self::DEFAULT_ALGORITHM;

    protected string $privateKey;

    protected string $publicKey;

    protected ?string $passphrase = null;

    public function withEnvironmentVariables(): self
    {
        $clone = clone $this;

        if (!empty(getenv(self::ENV_VAR_PRIVATE_KEY))) {
            $clone->privateKey = getenv(self::ENV_VAR_PRIVATE_KEY);
        }

        if (!empty(getenv(self::ENV_VAR_PUBLIC_KEY))) {
            $clone->publicKey = getenv(self::ENV_VAR_PUBLIC_KEY);
        }

        if (!empty(getenv(self::ENV_VAR_PASSPHRASE))) {
            $clone->passphrase = getenv(self::ENV_VAR_PASSPHRASE);
        }

        if (!empty(getenv(self::ENV_VAR_ALGORITHM))) {
            $clone->algorithm = getenv(self::ENV_VAR_ALGORITHM);
        }

        return $clone;
    }

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

    public function getPassphrase(): ?string
    {
        return $this->passphrase;
    }

    public function withPassphrase(?string $passphrase): self
    {
        $clone = clone $this;
        $clone->passphrase = $passphrase;

        return $clone;
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function create(\DateTimeInterface $expiresAt, array $payload): string
    {
        $this->assertPrivateKey();

        $payload['exp'] = $expiresAt->getTimestamp();
        $payload['iat'] = (new \DateTime())->getTimestamp();

        $privateKey = $this->resolvePrivateKey();

        if (self::ALGORITHM_HS256 === $this->algorithm) {
            return JWT::encode($payload, $privateKey, $this->algorithm);
        }

        if (self::ALGORITHM_RS256 === $this->algorithm) {
            $privateKey = !$this->passphrase ? $privateKey : openssl_pkey_get_private($privateKey, $this->passphrase);

            return JWT::encode($payload, $privateKey, $this->algorithm);
        }

        throw new ShouldNotHappenException('Algorithm was given, but not handled.', ['alg' => $this->algorithm]);
    }

    public function verify(string $token): bool
    {
        try {
            $this->read($token);
        } catch (\DomainException|\UnexpectedValueException|SignatureInvalidException|BeforeValidException|ExpiredException) {
            return false;
        } catch (\Throwable $e) {
            throw new InternalErrorException('Unexpected issue on decoding jwt token: '.$e->getMessage());
        }

        return true;
    }

    public function isExpired(string $token): bool
    {
        try {
            $this->read($token);
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
    public function read(string $token): array
    {
        if (empty($this->publicKey)) {
            $this->assertPrivateKey();
        }

        $data = JWT::decode($token, new Key($this->publicKey ?? $this->privateKey, $this->algorithm));

        /* @noinspection JsonEncodingApiUsageInspection */
        return json_decode(json_encode($data), true);
    }

    protected function resolvePrivateKey(): string
    {
        if (!str_starts_with($this->privateKey, self::FILE_REFERENCE_PREFIX)) {
            return $this->privateKey;
        }

        $path = str_replace(
            self::FILE_REFERENCE_PREFIX,
            rtrim(Environment::getProjectPath(), '/').'/',
            $this->privateKey
        );

        return file_get_contents($path);
    }

    protected function assertPrivateKey(): self
    {
        if (empty($this->privateKey)) {
            throw new InternalErrorException('Private key was not set');
        }

        return $this;
    }
}
