<?php

namespace Crell\Bundle\Planedo\Tests\TestApplication;

use Crell\Bundle\Planedo\PlanedoBundle;
use DAMA\DoctrineTestBundle\DAMADoctrineTestBundle;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle;
use EasyCorp\Bundle\EasyAdminBundle\EasyAdminBundle;
use Psr\Clock\ClockInterface;
use Psr\Http\Client\ClientInterface;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel as SymfonyKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

class Kernel extends SymfonyKernel
{
    use MicroKernelTrait {
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
            new PlanedoBundle(),
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

        // Load our bundle's services from the bundle directly.
        // @todo This seems to have no effect, even though the file is getting loaded.
        // That's why, for now, the services.yaml file is manually duplicated in the test app.
        $bundleConfigFile = realpath($this->getProjectDir() . '/../../config/services.yaml');
        if (is_file($bundleConfigFile)) {
            $container->import($bundleConfigFile);
        }
    }

    protected function buildContainer(): ContainerBuilder
    {
        $container =  parent::buildContainer();

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
        $routes->import($this->getProjectDir() . '/../src/config/routes_admin.yaml')->prefix('/');
        $routes->import($this->getProjectDir() . '/../src/config/routes_public.yaml')->prefix('/');
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
