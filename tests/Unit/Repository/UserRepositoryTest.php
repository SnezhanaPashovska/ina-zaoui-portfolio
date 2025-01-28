<?php

namespace App\Tests\Repository;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\DataFixtures\AppFixturesTest;

class UserRepositoryTest extends KernelTestCase
{
  private EntityManagerInterface $entityManager;
  private UserRepository $userRepository;
  private $client = null;

  public function setUp(): void
  {
    parent::setUp();

    $this->entityManager = self::getContainer()->get('doctrine')->getManager();
    $this->userRepository = self::getContainer()->get(UserRepository::class);

    $this->clearUsers();

    $this->loadFixtures(
      self::getContainer()->get('security.password_hasher'),
      ['admin', 'guest']
    );
  }

  private function clearUsers(): void
  {
    $users = $this->entityManager->getRepository(User::class)->findAll();

    foreach ($users as $user) {
      $this->entityManager->remove($user);
    }
    $this->entityManager->flush();
  }

  private function loadFixtures(UserPasswordHasherInterface $passwordHasher, array $tags): void
  {
    $manager = self::getContainer()->get('doctrine')->getManager();

    $fixture = new \App\DataFixtures\AppFixturesTest($passwordHasher, $tags);

    $fixture->load($manager);
    $manager->flush();
  }

  public function testUpgradePassword(): void
  {
    $this->clearUsers(); 

    $this->loadFixtures(
      self::getContainer()->get('security.password_hasher'),
      ['admin', 'guest'] 
    );

    $user = $this->userRepository->findOneBy(['email' => 'guest@example.com']);

    $this->assertNotNull($user, "User not found in database");

    $newPassword = '$2y$04$IeNqTlzSlxK8rUvHsBklkuJzn6b9rW0JulJRvil/TKBHt9TxOnPIS';
    $this->userRepository->upgradePassword($user, $newPassword);

    $this->entityManager->refresh($user);

    $this->assertEquals($newPassword, $user->getPassword());
  }

  public function testFindAdmins()
  {
    $this->clearUsers();
    $this->loadFixtures(
      self::getContainer()->get('security.password_hasher'),
      ['admin']
    );
    $admins = $this->userRepository->findAdmins();
    $this->assertNotNull($admins);
  }

}
