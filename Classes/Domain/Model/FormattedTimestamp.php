<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Domain\Model;

use RozbehSharahi\Graphql3\Exception\GraphqlException;

class FormattedTimestamp
{
    public function __construct(protected int $value, protected string $format = 'Y-m-d h:i')
    {
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function getFormat(): string
    {
        return $this->format;
    }

    public function __toString()
    {
        try {
            return (new \DateTimeImmutable('@'.$this->value))->format($this->format);
        } catch (\Exception) {
            throw GraphqlException::createClientSafe('Given timestamp was not valid');
        }
    }
}
