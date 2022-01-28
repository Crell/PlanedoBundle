<?php

declare(strict_types=1);

namespace Crell\Bundle\Planedo\Tests;

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

    private ContainerInterface $container;

    protected function entityManager(): EntityManagerInterface
    {
        return $this->em ??= $this->getEntityManager();
    }

    protected function getEntityManager(): EntityManagerInterface
    {
        $container = $this->container;
        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);
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

    //abstract protected function getContainer(): ContainerInterface;

}
