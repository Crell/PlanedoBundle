<?php

declare(strict_types=1);

namespace Crell\Bundle\Planedo\Tests;

use Crell\Bundle\Planedo\Tests\TestApplication\Kernel;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class DummyTest extends TestCase
{
    protected static Kernel $kernel;

    protected static function getContainer(): ContainerInterface
    {
        return self::$kernel->getContainer();
    }

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$kernel = new Kernel();
        self::$kernel->boot();
    }

    /**
     * @test
     */
    public function dummy(): void
    {
//        $kernel = new PlanedoTestingKernel('test', true);
//        $kernel->boot();

//        $container = $kernel->getContainer();
        $container = self::getContainer();

    }

}
