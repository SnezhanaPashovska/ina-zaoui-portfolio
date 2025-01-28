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
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    /**
     * Fetch all admins.
     * 
     * @return User[] Returns an array of admin User objects
     */
    public function findAdmins(): ?User
    {
        return $this->createQueryBuilder('u')
            ->where('u.admin = :admin')
            ->setParameter('admin', true)
            ->orderBy('u.name', 'ASC')
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findActiveUsersPaginated(int $page, int $limit): Paginator
    {
        $queryBuilder = $this->createQueryBuilder('u')
            ->where('u.admin = :admin')
            ->setParameter('admin', false)
            ->leftJoin('u.medias', 'm')
            ->addSelect('m')
            ->orderBy('u.name', 'ASC');

        $query = $queryBuilder->getQuery();

        $query->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        return new Paginator($query, true);
    }
}
