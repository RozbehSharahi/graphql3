<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Converter;

use Doctrine\Inflector\Inflector;
use Doctrine\Inflector\InflectorFactory;

class CaseConverter
{
    private Inflector $inflector;

    public function __construct()
    {
        $this->inflector = InflectorFactory::create()->build();
    }

    public function toCamel(string $input): string
    {
        return $this->inflector->camelize($input);
    }

    public function toPascal(string $input): string
    {
        return ucfirst($this->toCamel($input));
    }

    public function toPascalSingular(string $input): string
    {
        return $this->inflector->singularize($this->toPascal($input));
    }

    public function toCamelSingular(string $input): string
    {
        return $this->inflector->singularize($this->toCamel($input));
    }

    public function toCamelPlural(string $input): string
    {
        return $this->inflector->pluralize($this->toCamel($input));
    }
}
