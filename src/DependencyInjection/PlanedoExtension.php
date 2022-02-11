<?php

declare(strict_types=1);

/*
 * This file is part of the package crell/planedo-bundle.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Crell\Bundle\Planedo\DependencyInjection;

use Crell\Bundle\Planedo\Repository\ResetPasswordRequestRepository;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

class PlanedoExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('planedo.itemsPerPage', $config['items_per_page']);
        $container->setParameter('planedo.purgeBefore', $config['purge_before']);
        $container->setParameter('planedo.usePlainText', $config['use_plain_text']);

        // Load this bundle's services.
        $loader = new YamlFileLoader($container, new FileLocator(dirname(__DIR__) . '/../config'));
        $loader->load('services.yaml');
    }

    public function prepend(ContainerBuilder $container): void
    {
        if ($container->hasExtension('symfonycasts_reset_password')) {
            $container->prependExtensionConfig(
                'symfonycasts_reset_password',
                [
                    'request_password_repository' => ResetPasswordRequestRepository::class,
                ]
            );
        }
        if ($container->hasExtension('security')) {
            $container->prependExtensionConfig(
                'security',
                [
                    'password_hashers' => [
                        PasswordAuthenticatedUserInterface::class => 'auto',
                        User::class => [
                            'algorithm' => 'auto',
                        ],
                    ],
                ]
            );
        }
    }
}
