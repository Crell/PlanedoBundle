<?php

declare(strict_types=1);

namespace Crell\Bundle\Planedo\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class CrellPlanedoExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
//        $loader = new YamlFileLoader($container, new FileLocator(dirname(__DIR__) . '/Resources/config'));
        $loader = new YamlFileLoader($container, new FileLocator(dirname(__DIR__) . '/../config'));
        $loader->load('services.yaml');
    }
}
