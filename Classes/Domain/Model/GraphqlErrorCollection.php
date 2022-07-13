<?php

namespace RozbehSharahi\Graphql3\Domain\Model;

use RozbehSharahi\Graphql3\Exception\GraphqlException;

class GraphqlErrorCollection
{
    public static function createSingleError(string $message): self
    {
        return new self([new GraphqlError($message)]);
    }

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
                throw new GraphqlException(self::class.' only allows '.GraphqlError::class.' items.');
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

    public function toArray(): array
    {
        return array_map(static fn ($error) => $error->toArray(), $this->errors);
    }
}
