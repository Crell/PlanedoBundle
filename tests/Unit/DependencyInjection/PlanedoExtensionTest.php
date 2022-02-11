<?php

declare(strict_types=1);

/*
 * This file is part of the package crell/planedo-bundle.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Crell\Bundle\Planedo\Tests\Unit\DependencyInjection;

use Crell\Bundle\Planedo\DependencyInjection\PlanedoExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class PlanedoExtensionTest extends TestCase
{
    /** @var ContainerBuilder */
    protected $configuration;

    protected function tearDown(): void
    {
        $this->configuration = null;
    }

    public function testDefault()
    {
        $container = new ContainerBuilder();
        $loader = new PlanedoExtension();
        $loader->load([[]], $container);

        self::assertEquals(10, $container->getParameter('planedo.itemsPerPage'));
        self::assertEquals('-30 days', $container->getParameter('planedo.purgeBefore'));
        self::assertFalse($container->getParameter('planedo.usePlainText'));
    }
}
