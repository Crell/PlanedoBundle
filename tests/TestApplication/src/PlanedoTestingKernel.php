<?php

declare(strict_types=1);

/*
 * This file is part of the package crell/planedo-bundle.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Crell\Bundle\Planedo\Tests\TestApplication;

use Crell\Bundle\Planedo\CrellPlanedoBundle;
use Crell\Bundle\Planedo\FeedReaderClient;
use DAMA\DoctrineTestBundle\DAMADoctrineTestBundle;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle;
use EasyCorp\Bundle\EasyAdminBundle\EasyAdminBundle;
use Laminas\Feed\Reader\Http\ClientInterface;
use Psr\Clock\ClockInterface;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Symfony\Component\Yaml\Yaml;

class PlanedoTestingKernel extends Kernel implements CompilerPassInterface
{
    use MicroKernelTrait {
//        MicroKernelTrait::registerContainerConfiguration as microkernelContainerConfig;
            MicroKernelTrait::configureContainer as microkernelConfigureContainer;
    }

    protected array $config;

    protected array $defaultConfig = [
        'items_per_page' => 10,
        'purge_before' => '-30 days',
        'use_plain_text' => false,
    ];

    public function __construct(array $config = [])
    {
        parent::__construct('test', true);
        $this->config = $config + $this->defaultConfig;
    }

    public function registerBundles(): iterable
    {
        return [
            new FrameworkBundle(),
            new SecurityBundle(),
            new EasyAdminBundle(),
            new TwigBundle(),
            new DoctrineBundle(),
            new DAMADoctrineTestBundle(),
            new DoctrineFixturesBundle(),
            new CrellPlanedoBundle(),
        ];
    }

    public function configureContainer(ContainerConfigurator $container, LoaderInterface $loader, ContainerBuilder $builder): void
    {
        $this->microkernelConfigureContainer($container, $loader, $builder);

        // Load package configuration for our dependencies.
        $configDir = $this->getConfigDir();
        $loader->load($configDir . '/*.yaml', 'glob');
        $loader->load($configDir . '/{packages}/*.yaml', 'glob');
        $loader->load($configDir . '/{packages}/' . $this->environment . '/*.yaml', 'glob');
        $loader->load($configDir . '/{services}.yaml', 'glob');
        $loader->load($configDir . '/{services}_' . $this->environment . '.yaml', 'glob');

        $bundleConfigFile = realpath($this->getProjectDir() . '/../../config/services.yaml');
        if (is_file($bundleConfigFile)) {
            $container->import($bundleConfigFile);
        }

        // Load our bundle's services from the bundle directly.
//        $path = realpath($this->getProjectDir() . '/../../config/services.yaml');
//        printf("\nPath: %s, Exists: %d\n", $path, file_exists($path));

//        printf("\nContainer type: %s\n", get_class($container));
    }

//    public function registerContainerConfiguration(LoaderInterface $loader): void
//    {
//        $this->microkernelContainerConfig($loader);
//
//        $loader->load(function(ContainerBuilder $container) {
//            $container->register(ClientInterface::class, FeedReaderClient::class);
//            $container->loadFromExtension('crell_planedo', $this->config);
//
    ////            $this->loadExtensionConfigFixture('framework', $container);
    ////            $this->loadExtensionConfigFixture('doctrine', $container);
    ////            $this->loadExtensionConfigFixture('security', $container);
//
//            $container->register(UserPasswordHasherInterface::class, UserPasswordHasher::class);
//        });
//    }

//    protected function loadExtensionConfigFixture(string $key, ContainerBuilder $container): void
//    {
//        $config = Yaml::parseFile(__DIR__ . "/test-configs/$key.yaml");
//        $container->loadFromExtension($key, $config[$key]);
//    }

    protected function buildContainer(): ContainerBuilder
    {
        $container = parent::buildContainer();

        $container->setParameter('kernel.secret', 'beep');

        return $container;
    }

    public function process(ContainerBuilder $container): void
    {
        $container->getDefinition(ClockInterface::class)->setPublic(true);
        $container->getDefinition(ClientInterface::class)->setPublic(true);
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        $routes->import(__DIR__ . '/../src/config/routes_admin.yaml')->prefix('/');
        $routes->import(__DIR__ . '/../src/config/routes_public.yaml')->prefix('/');
    }

    public function getCacheDir(): string
    {
        return __DIR__ . '/cache/' . spl_object_hash($this);
    }

    public function getProjectDir(): string
    {
        return \dirname(__DIR__);
    }
}
