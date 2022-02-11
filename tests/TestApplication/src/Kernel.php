<?php

declare(strict_types=1);

/*
 * This file is part of the package crell/planedo-bundle.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Crell\Bundle\Planedo\Tests\TestApplication;

use Crell\Bundle\Planedo\PlanedoBundle;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle;
use EasyCorp\Bundle\EasyAdminBundle\EasyAdminBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as SymfonyKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use SymfonyCasts\Bundle\ResetPassword\SymfonyCastsResetPasswordBundle;

class Kernel extends SymfonyKernel
{
    use MicroKernelTrait;

    protected array $config;

    protected array $defaultConfig = [
        'items_per_page' => 10,
        'purge_before' => '-30 days',
        'use_plain_text' => false,
    ];

    public function __construct()
    {
        parent::__construct('test', true);
    }

    public function registerBundles(): iterable
    {
        return [
            new FrameworkBundle(),
            new TwigBundle(),
            new DoctrineBundle(),
            new DoctrineFixturesBundle(),
            new SecurityBundle(),
            new SymfonyCastsResetPasswordBundle(),
            new EasyAdminBundle(),
            new PlanedoBundle(),
        ];
    }

    public function getProjectDir(): string
    {
        return \dirname(__DIR__);
    }

    public function getCacheDir(): string
    {
        return sys_get_temp_dir() . '/com.github.crell.planedobundle/tests/var/' . $this->environment . '/log';
    }

    public function getLogDir(): string
    {
        return sys_get_temp_dir() . '/com.github.crell.planedobundle/tests/var/' . $this->environment . '/log';
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        $routes->import($this->getProjectDir() . '/config/routes.php');
    }

    protected function configureContainer(ContainerBuilder $containerBuilder, LoaderInterface $loader): void
    {
        $loader->load($this->getProjectDir() . '/config/{packages}/*.php', 'glob');
        $loader->load($this->getProjectDir() . '/config/{packages}/' . $this->environment . '/*.php', 'glob');
        $loader->load($this->getProjectDir() . '/config/{services}.php', 'glob');
        $loader->load($this->getProjectDir() . '/config/{services}_' . $this->environment . '.php', 'glob');
    }
}
