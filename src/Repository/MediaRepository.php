<?php

namespace App\Repository;

use App\Entity\Media;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Media>
 *
 * @method Media|null find($id, $lockMode = null, $lockVersion = null)
 * @method Media|null findOneBy(array<string, mixed> $criteria, array<string, string>|null $orderBy = null)
 * @method Media[]    findAll()
 * @method Media[]    findBy(array<string, mixed> $criteria, array<string, string>|null $orderBy = null, $limit = null, $offset = null)
 */
class MediaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Media::class);
    }

    /**
     * Fetches media with related User and Album entities in a single query.
     *
     * @param int $userId Filter by user ID.
     * @param int $page Page number.
     * @param int $limit Number of items per page.
     * @return Media[] Returns an array of Media objects.
     */

    public function findPaginatedMediaByUser(int $userId, int $page, int $limit): array
    {
        return $this->createQueryBuilder('m')
            ->innerJoin('m.user', 'u')
            ->leftJoin('m.album', 'a')
            ->where('m.user = :userId')
            ->setParameter('userId', $userId)
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->addSelect('u', 'a')
            ->getQuery()
            ->getResult();
    }

    /**
     * Fetches paginated media, optionally filtered by album ID.
     *
     * @param int $page Page number.
     * @param int $limit Number of items per page.
     * @param int|null $albumId Optional album ID to filter by.
     * @return Media[] Returns an array of Media objects.
     */
    public function findPaginatedMedia(int $page, int $limit, ?int $albumId = null): array
    {
        $qb = $this->createQueryBuilder('m')
            ->innerJoin('m.user', 'u')
            ->andWhere('u.isActive = :isActive')
            ->setParameter('isActive', true)
            ->orderBy('m.id', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        if ($albumId !== null) {
            $qb->andWhere('m.album = :albumId')
                ->setParameter('albumId', $albumId);
        }

        return $qb->getQuery()->getResult();
    }
}
