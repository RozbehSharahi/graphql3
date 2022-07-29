<?php

namespace RozbehSharahi\Graphql3\Domain\Model;

class Page
{
    public function __construct(protected array $data)
    {
    }

    public function getData(): array
    {
        return $this->data;
    }
}
