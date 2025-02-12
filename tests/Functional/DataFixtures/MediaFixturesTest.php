<?php

namespace App\Tests\Functional\DataFixtures;

use App\DataFixtures\MediaFixtures;
use App\Tests\AppFixturesTest;
use App\Entity\Album;
use App\Entity\Media;
use App\Entity\User;
use PHPUnit\Framework\Assert;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class MediaFixturesTest extends WebTestCase
{
  private ?EntityManagerInterface $entityManager = null;

  protected function setUp(): void
  {
    parent::setUp();

    self::bootKernel();
    $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);

    $this->loadFixtures();
  }

  protected function loadFixtures(): void
  {
    $container = static::getContainer();
    $passwordHasher = $container->get(UserPasswordHasherInterface::class);

    // Load the fixtures
    $loader = new Loader();
    $loader->addFixture(new AppFixturesTest($passwordHasher, ['admin', 'guest', 'albums', 'media']));

    $purger = new ORMPurger($this->entityManager);
    $executor = new ORMExecutor($this->entityManager, $purger);
    $executor->execute($loader->getFixtures());
  }

  public function testMediaFixture(): void
    {
        // Check if the entity manager is not null
        Assert::assertNotNull($this->entityManager);

        // Retrieve the media repository and check if the media exists
        $mediaRepository = $this->entityManager->getRepository(Media::class);
        $media = $mediaRepository->findOneBy(['title' => 'Alpes']);
        Assert::assertNotNull($media);
    }

    public function testMediaRelationships(): void
    {
        // Check if the entity manager is not null
        Assert::assertNotNull($this->entityManager);

        // Retrieve the media repository and check if relationships are set correctly
        $mediaRepository = $this->entityManager->getRepository(Media::class);
        $media = $mediaRepository->findOneBy(['title' => 'Alpes']);

        // Explicit null check for the media entity
        if ($media !== null) {
            Assert::assertNotNull($media->getUser()); // User relationship
            Assert::assertNotNull($media->getAlbum()); // Album relationship
        } else {
            Assert::fail("Media 'Alpes' not found.");
        }
    }
  
  protected function tearDown(): void
  {
    parent::tearDown();
    // Explicitly check if entityManager is not null
    if ($this->entityManager !== null) {
      $this->entityManager->close();
      $this->entityManager = null;
    }
  }
}
