<?php

namespace App\Tests\Repository;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Doctrine\ORM\EntityManagerInterface;

class UserRepositoryTest extends KernelTestCase
{
  private EntityManagerInterface $entityManager;
  private UserRepository $userRepository;

  protected function setUp(): void
  {
    self::bootKernel();

    $container = self::getContainer();

    $this->entityManager = $container->get(EntityManagerInterface::class);
    $this->userRepository = $container->get(UserRepository::class);
  }

  public function testUpgradePassword(): void
  {
    $user = new User();
    $user->setUsername('john_doe')
      ->setEmail('john.doe@example.com')
      ->setPassword('old_password');

    $this->entityManager->persist($user);
    $this->entityManager->flush();

    $newPassword = 'new_hashed_password';
    $this->userRepository->upgradePassword($user, $newPassword);

    $this->entityManager->refresh($user);

    $this->assertEquals($newPassword, $user->getPassword());
  }
}
