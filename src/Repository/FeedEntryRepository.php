<?php

declare(strict_types=1);

/*
 * This file is part of the package crell/planedo-bundle.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Crell\Bundle\Planedo\Repository;

use Crell\Bundle\Planedo\Entity\FeedEntry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method FeedEntry|null find($id, $lockMode = null, $lockVersion = null)
 * @method FeedEntry|null findOneBy(array $criteria, array $orderBy = null)
 * @method FeedEntry[]    findAll()
 * @method FeedEntry[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FeedEntryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, protected int $itemsPerPage)
    {
        parent::__construct($registry, FeedEntry::class);
    }

    public function latestEntriesPaginator(int $offset): Paginator
    {
        $class = FeedEntry::class;
        // Filter out unapproved entries and entries of a feed that is disabled.
        $query = $this->getEntityManager()
            ->createQuery("SELECT e FROM $class e
                JOIN e.feed f
                WHERE e.approved = :approved
                    AND f.active = :activeFeed
                ORDER BY e.dateModified DESC")
            ->setMaxResults($this->itemsPerPage)
            ->setFirstResult($offset)
            ->setParameter('approved', true)
            ->setParameter('activeFeed', true);

        return new Paginator($query);
    }

    /**
     * Purge feed entries older than a certain timestamp.
     *
     * @param \DateTimeImmutable $threshold
     *   The date older than which entries should be purged
     */
    public function deleteOlderThan(\DateTimeImmutable $threshold): void
    {
        $this->_em->createQueryBuilder()
            ->delete(FeedEntry::class, 'e')
            ->where('e.dateModified < :threshold')
            ->setParameter('threshold', $threshold)
            ->getQuery()
            ->getResult();
    }

    /**
     * Marks multiple entries as approved, so they show in feeds.
     *
     * @param string ...$ids
     *   A list of the IDs (links) to approve.
     */
    public function approve(string ...$ids): void
    {
        $this->_em->createQueryBuilder()
            ->update(FeedEntry::class, 'e')
            ->set('e.approved', ':approved')
            ->where('e.link IN (:ids)')
            ->setParameter('approved', true)
            ->setParameter('ids', $ids, Connection::PARAM_STR_ARRAY)
            ->getQuery()
            ->getResult();
    }

    /**
     * Marks multiple entries as not approved, so they do not show in feeds.
     *
     * @param string ...$ids
     *   A list of the IDs (links) to reject.
     */
    public function reject(string ...$ids): void
    {
        $this->_em->createQueryBuilder()
            ->update(FeedEntry::class, 'e')
            ->set('e.approved', ':approved')
            ->where('e.link IN (:ids)')
            ->setParameter('approved', false)
            ->setParameter('ids', $ids, Connection::PARAM_STR_ARRAY)
            ->getQuery()
            ->getResult();
    }

    public function getApprovedEntryCount(): int
    {
        return $this->count(['approved' => 'true']);
    }

    // /**
    //  * @return FeedEntry[] Returns an array of FeedEntry objects
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
    public function findOneBySomeField($value): ?FeedEntry
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
