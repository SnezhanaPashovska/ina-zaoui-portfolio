<?php

namespace App\Repository;

use App\Entity\Media;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Media>
 *
 * @method Media|null find($id, $lockMode = null, $lockVersion = null)
 * @method Media|null findOneBy(array $criteria, array $orderBy = null)
 * @method Media[]    findAll()
 * @method Media[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
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
     * @param int|null $userId Filter by user ID, or null to fetch all media.
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

    public function findPaginatedMedia(int $page, int $limit, ?int $albumId = null)
    {
        $qb = $this->createQueryBuilder('m')
            ->innerJoin('m.user', 'u')
            ->andWhere('u.isActive = :isActive')
            ->setParameter('isActive', true)
            ->orderBy('m.id', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        if ($albumId) {
            $qb->andWhere('m.album = :albumId')
                ->setParameter('albumId', $albumId);
        }
    }
}
