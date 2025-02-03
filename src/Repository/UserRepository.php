<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @extends ServiceEntityRepository<User>
 * @implements PasswordUpgraderInterface<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, User::class);
        $this->entityManager = $entityManager;
    }

    public function save(User $user, bool $flush = true): void
    {
        $this->entityManager->persist($user);

        if ($flush) {
            $this->entityManager->flush();
        }
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        // No need for instanceof check as the type is guaranteed by the type hint
        $user->setPassword($newHashedPassword);
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    /**
     * Fetch all admins.
     * 
     * @return User|null Returns an admin User object or null
     */
    public function findAdmins(): ?User
    {
        return $this->createQueryBuilder('u')
            ->where('u.admin = :admin')
            ->setParameter('admin', true)
            ->orderBy('u.name', 'ASC')
            ->getQuery()
            ->getOneOrNullResult();

        if ($admin instanceof User) {
            return $admin;
        }

        return null;
    }

    /**
     * Fetch paginated active users.
     * 
     * @param int $page
     * @param int $limit
     * @return Paginator<User>
     */
    public function findActiveUsersPaginated(int $page, int $limit): Paginator
    {
        $queryBuilder = $this->createQueryBuilder('u')
            ->where('u.admin = :admin')
            ->andWhere('u.isActive = :active')
            ->setParameter('admin', false)
            ->setParameter('active', true)
            ->leftJoin('u.medias', 'm')
            ->addSelect('m');

        $query = $queryBuilder->getQuery();

        $query->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        return new Paginator($query, true);
    }

    /**
     * Fetch paginated users.
     * 
     * @param int $page
     * @param int $limit
     * @return Paginator<User>
     */
    public function findAllPaginated(int $page, int $limit): Paginator
    {
        $query = $this->createQueryBuilder('u')
            ->where('u.admin = false')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery();

        return new Paginator($query);
    }
}
