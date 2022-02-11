<?php

declare(strict_types=1);

/*
 * This file is part of the package crell/planedo-bundle.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Crell\Bundle\Planedo\Repository;

use Crell\Bundle\Planedo\Entity\Feed;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Feed|null find($id, $lockMode = null, $lockVersion = null)
 * @method Feed|null findOneBy(array $criteria, array $orderBy = null)
 * @method Feed[]    findAll()
 * @method Feed[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FeedRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, protected int $itemsPerPage)
    {
        parent::__construct($registry, Feed::class);
    }

    /**
     * Gets all feeds, sorted alphabetically by the site name.
     *
     * @return Feed[]
     */
    public function paginatedByName(int $offset): array
    {
        return $this->createQueryBuilder('f')
            ->orderBy('f.title', 'ASC')
            ->setMaxResults($this->itemsPerPage)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * Gets the feeds with the most number of feed entries.
     *
     * @param int $limit
     *
     * @return Feed[]
     */
    public function getMostActive(int $limit): array
    {
        $class = Feed::class;

        return $this->getEntityManager()
            ->createQuery("SELECT f FROM $class f
                WHERE f.active = true
                ORDER BY SIZE(f.entries) DESC, f.title ASC")
            ->setMaxResults($limit)
            ->execute();
    }

    public function getActiveFeedCount(): int
    {
        return $this->count(['active' => 'true']);
    }

    public function findOneByFeedLink(string $link): ?Feed
    {
        $class = Feed::class;

        $ret = $this->getEntityManager()
            ->createQuery("SELECT f FROM $class f
                WHERE f.feedLink = :link")
            ->setMaxResults(1)
            ->setParameter('link', $link)
            ->execute();

        return $ret[0] ?? null;
    }

    // /**
    //  * @return Feed[] Returns an array of Feed objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('f.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Feed
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
