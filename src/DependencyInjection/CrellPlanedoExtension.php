<?php

declare(strict_types=1);

/*
 * This file is part of the package crell/planedo-bundle.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Crell\Bundle\Planedo\DependencyInjection;

use Crell\Bundle\Planedo\Controller\BlogRollController;
use Crell\Bundle\Planedo\Controller\FeedController;
use Crell\Bundle\Planedo\Controller\HtmlFeedController;
use Crell\Bundle\Planedo\MessageHandler\PurgeOldEntriesHandler;
use Crell\Bundle\Planedo\MessageHandler\UpdateFeedHandler;
use Crell\Bundle\Planedo\Repository\FeedEntryRepository;
use Crell\Bundle\Planedo\Repository\FeedRepository;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

class CrellPlanedoExtension extends ConfigurableExtension
{
    protected function loadInternal(array $mergedConfig, ContainerBuilder $container)
    {
        // Load this bundle's services.
        $loader = new YamlFileLoader($container, new FileLocator(dirname(__DIR__) . '/../config'));
        $loader->load('services.yaml');

        // Wire up configuration.
        $setter = fn ($param, $value, $services) => $this->setConfig($container, $param, $value, $services);

        $setter('$itemsPerPage', $mergedConfig['items_per_page'], [
            HtmlFeedController::class,
            BlogRollController::class,
            FeedRepository::class,
            FeedEntryRepository::class,
        ]);

        $setter('$purgeBefore', $mergedConfig['purge_before'], [
            PurgeOldEntriesHandler::class,
            UpdateFeedHandler::class,
        ]);

        $setter('$plainTextFeeds', $mergedConfig['use_plain_text'], [
            FeedController::class,
        ]);
    }

    /**
     * Sets a parameter on the listed services based on its name.
     */
    protected function setConfig(ContainerBuilder $container, string $parameterName, mixed $value, array $services): void
    {
        foreach ($services as $service) {
            $container->getDefinition($service)
                ->setArgument($parameterName, $value);
        }
    }
}
