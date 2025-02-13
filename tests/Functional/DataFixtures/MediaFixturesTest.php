<?php

namespace App\Tests\Functional\DataFixtures;

use App\DataFixtures\MediaFixtures;
use App\DataFixtures\UserFixtures;
use App\DataFixtures\AlbumFixtures;
use App\Entity\Media;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class MediaFixturesTest extends WebTestCase
{
  private EntityManagerInterface $entityManager;

  protected function setUp(): void
  {
    parent::setUp();

    $client = static::createClient();
    $entityManager = $client->getContainer()->get('doctrine')->getManager();

    if (!$entityManager instanceof EntityManagerInterface) {
      throw new \RuntimeException('Entity manager is not an instance of EntityManagerInterface');
    }

    $this->entityManager = $entityManager;
    $this->loadFixtures();
  }

  protected function loadFixtures(): void
  {
    $container = static::getContainer();
    $hasher = $container->get(UserPasswordHasherInterface::class);
    $userFixtures = new UserFixtures($hasher);

    $albumFixtures = new AlbumFixtures();
    $mediaFixtures = new MediaFixtures();

    $purger = new ORMPurger($this->entityManager);
    $executor = new ORMExecutor($this->entityManager, $purger);

    $executor->execute([$userFixtures, $albumFixtures, $mediaFixtures]);
  }

  public function testMediaFixture(): void
  {
    $mediaCount = $this->entityManager->getRepository(Media::class)->count([]);

    static::assertGreaterThan(0, $mediaCount, 'No media found after fixture load.');
  }

  public function testMediaAttributes(): void
  {
    $media = $this->entityManager->getRepository(Media::class)->findOneBy(['title' => 'Beach']);

    static::assertNotNull($media, 'Media with title "Beach" not found.');
    static::assertSame('/uploads/0006.jpg', $media->getPath(), 'Media path does not match.');
    static::assertNotNull($media->getUser(), 'Media does not have an associated User.');
    static::assertNotNull($media->getAlbum(), 'Media does not have an associated Album.');
  }

  protected function tearDown(): void
  {
    parent::tearDown();

    if ($this->entityManager->isOpen()) {
      $this->entityManager->close();
    }
  }
}
