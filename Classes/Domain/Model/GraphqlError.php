<?php

namespace RozbehSharahi\Graphql3\Domain\Model;

class GraphqlError
{
    public function __construct(protected string $message)
    {
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function toArray(): array
    {
        return [
            'message' => $this->message,
        ];
    }
}
