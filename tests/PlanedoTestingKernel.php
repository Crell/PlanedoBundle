<?php

declare(strict_types=1);

namespace Crell\Bundle\Planedo\Tests;


use Crell\Bundle\Planedo\CrellPlanedoBundle;
use Crell\Bundle\Planedo\FeedReaderClient;
use DAMA\DoctrineTestBundle\DAMADoctrineTestBundle;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\EasyAdminBundle;
use Laminas\Feed\Reader\Http\ClientInterface;
use Psr\Clock\ClockInterface;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Messenger\DependencyInjection\MessengerPass;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

class PlanedoTestingKernel extends Kernel implements CompilerPassInterface
{
    protected array $config;

    public function __construct(array $config = [])
    {
        parent::__construct('test', true);
        $this->config = $config;
    }

    public function registerBundles(): iterable
    {
        return [
            new CrellPlanedoBundle(),
            new FrameworkBundle(),
            new EasyAdminBundle(),
            new TwigBundle(),
            new DoctrineBundle(),
            new DAMADoctrineTestBundle(),
            new DoctrineFixturesBundle(),
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(function(ContainerBuilder $container) {
            $container->register(ClientInterface::class, FeedReaderClient::class);
            $container->loadFromExtension('crell_planedo', $this->config);

            $container->loadFromExtension('doctrine', [
                'dbal' => [
                    'url' => 'postgresql://postgres@database:5432/symfony_test?serverVersion=13&charset=utf8'
                ],
                'orm' => [
                    'auto_generate_proxy_classes' => true,
                    'naming_strategy' =>  'doctrine.orm.naming_strategy.underscore_number_aware',
                    'auto_mapping' =>  true,
                ],
            ]);

            //$container->loadFromExtension('security', []);
            $container->register(UserPasswordHasherInterface::class, UserPasswordHasher::class);
            //$container->register(EntityManagerInterface::class, EntityManager::class);

            //$container->register(PasswordAuthenticatedUserInterface::class, PasswordAuthenticatedUserInterface::class);
        });
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
        $routes->import(__DIR__.'/../src/config/routes_admin.yaml', '/');
        $routes->import(__DIR__.'/../src/config/routes_public.yaml', '/');
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
