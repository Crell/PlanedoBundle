<?php

declare(strict_types=1);

/*
 * This file is part of the package crell/planedo-bundle.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Crell\Bundle\Planedo\Tests\Functional;

use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Component\HttpKernel\KernelInterface;

trait DatabasePrimer
{
    public function prime(): void
    {
        /** @var KernelInterface $kernel */
        $kernel = self::$kernel;

        // Make sure we are in the test environment
        if ('test' !== $kernel->getEnvironment()) {
            throw new \LogicException('Primer must be executed in the test environment');
        }

        // Get the entity manager from the service container
        $entityManager = $kernel->getContainer()->get('doctrine.orm.entity_manager');

        // Recreate the database
        $metadatas = $entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->dropDatabase();
        $schemaTool->createSchema($metadatas);
    }
}
