<?php

declare(strict_types=1);

/*
 * This file is part of the package crell/planedo-bundle.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

// Required by EasyAdmin 5; mirrors the Symfony Flex recipe for symfony/ux-twig-component.
return static function (ContainerConfigurator $container): void {
    $container->extension('twig_component', [
        'anonymous_template_directory' => 'components/',
        'defaults' => [
            'Crell\\Bundle\\Planedo\\Tests\\TestApplication\\Twig\\Components\\' => 'components/',
        ],
    ]);
};
