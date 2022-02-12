<?php

declare(strict_types=1);

/*
 * This file is part of the package crell/planedo-bundle.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Crell\Bundle\Planedo\Tests\Functional;

use Crell\Bundle\Planedo\Entity\Feed;
use Crell\Bundle\Planedo\Entity\FeedEntry;
use Crell\Bundle\Planedo\Entity\User;
use Crell\Bundle\Planedo\Repository\FeedEntryRepository;
use Crell\Bundle\Planedo\Repository\FeedRepository;
use Crell\Bundle\Planedo\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;

trait EntityManagerWrapper
{
    private EntityManagerInterface $em;

    protected function entityManager(): EntityManagerInterface
    {
        return $this->em ??= $this->getEntityManager();
    }

    protected function getEntityManager(): EntityManagerInterface
    {
        /** @var EntityManagerInterface $em */
        $em = self::getContainer()->get(EntityManagerInterface::class);

        return $em;
    }

    protected function feedRepo(): FeedRepository
    {
        return $this->entityManager()->getRepository(Feed::class);
    }

    protected function feedEntryRepo(): FeedEntryRepository
    {
        return $this->entityManager()->getRepository(FeedEntry::class);
    }

    protected function userRepo(): UserRepository
    {
        return $this->entityManager()->getRepository(User::class);
    }

    abstract protected static function getContainer(): ContainerInterface;
}
