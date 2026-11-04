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
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel as SymfonyKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Symfony\UX\TwigComponent\TwigComponentBundle;
use SymfonyCasts\Bundle\ResetPassword\SymfonyCastsResetPasswordBundle;
use Twig\Extra\TwigExtraBundle\TwigExtraBundle;

class Kernel extends SymfonyKernel
{
    use MicroKernelTrait;

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
            // EasyAdmin 5 builds its UI from Twig Components and uses the twig/html-extra
            // functions, so both of these bundles are mandatory.
            new TwigComponentBundle(),
            new TwigExtraBundle(),
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
        return $this->getProjectDir() . '/var/cache/' . $this->environment;
    }

    public function getLogDir(): string
    {
        return $this->getProjectDir() . '/var/log/' . $this->environment;
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        $routes->import($this->getProjectDir() . '/config/routes.php');
    }

    protected function configureContainer(ContainerConfigurator $container): void
    {
        $configDir = $this->getProjectDir() . '/config';

        $container->import($configDir . '/{packages}/*.php');
        $container->import($configDir . '/{packages}/' . $this->environment . '/*.php');
        $container->import($configDir . '/{services}.php');
        $container->import($configDir . '/{services}_' . $this->environment . '.php');
    }
}
