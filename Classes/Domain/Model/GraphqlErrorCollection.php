<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Domain\Model;

use JsonException;
use RozbehSharahi\Graphql3\Exception\InternalErrorException;

class GraphqlErrorCollection
{
    public static function createSingleError(string $message): self
    {
        return new self([new GraphqlError($message)]);
    }

    /**
     * @param GraphqlError[] $errors
     */
    public static function createFromArray(array $errors): self
    {
        return new self(array_map(static fn ($v) => new GraphqlError($v['message']), $errors));
    }

    /**
     * @param GraphqlError[] $errors
     */
    public function __construct(protected array $errors)
    {
        foreach ($this->errors as $error) {
            if (!$error instanceof GraphqlError) {
                throw new InternalErrorException(self::class.' only allows '.GraphqlError::class.' items.');
            }
        }
    }

    /**
     * @return GraphqlError[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @return array<int, array<string, string>>
     */
    public function toArray(): array
    {
        return array_map(static fn ($error) => $error->toArray(), $this->errors);
    }

    public function toJson(): string
    {
        try {
            return json_encode(['errors' => $this->toArray()], JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new InternalErrorException('Could not encode graphql error to json: '.$e->getMessage());
        }
    }
}
