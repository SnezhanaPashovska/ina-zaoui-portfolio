<?php

namespace App\Tests\Functional\DataFixtures;

use App\Tests\AppFixturesTest;
use App\Entity\Album;
use App\Entity\Media;
use App\Entity\User;
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
        $this->assertNotNull($this->entityManager, 'EntityManager is not initialized.');
        $mediaCount = $this->entityManager->getRepository(Media::class)->count([]);
        static::assertGreaterThan(0, $mediaCount, 'No media found after fixture load.');
    }

    public function testMediaRelationships(): void
    {
        $this->assertNotNull($this->entityManager, 'EntityManager is not initialized.');
        $media = $this->entityManager->getRepository(Media::class)->findOneBy(['title' => 'Alpes']);
        static::assertNotNull($media);
        static::assertInstanceOf(User::class, $media->getUser());
        static::assertInstanceOf(Album::class, $media->getAlbum());
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        if ($this->entityManager) {
            $this->entityManager->close();
            $this->entityManager = null;
        }
    }
}

