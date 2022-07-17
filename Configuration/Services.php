<?php

declare(strict_types=1);
namespace RozbehSharahi\Graphql3;

use RozbehSharahi\Graphql3\Setup\SetupInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container, ContainerBuilder $containerBuilder) {
    $containerBuilder
        ->registerForAutoconfiguration(SetupInterface::class)
        ->addTag('graphql3.setup');
};
