<?php

namespace App\Tests\DataFixtures;

use App\DataFixtures\AlbumFixtures;
use App\Entity\Album;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AlbumFixturesTest extends WebTestCase
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
        $loader = new Loader();
        $loader->addFixture(new AlbumFixtures());

        $purger = new ORMPurger($this->entityManager);
        $executor = new ORMExecutor($this->entityManager, $purger);
        $executor->execute($loader->getFixtures());
    }

    public function testAlbumsFixture(): void
    {
        $albumCount = $this->entityManager->getRepository(Album::class)->count([]);

        static::assertGreaterThan(0, $albumCount, 'No albums found after fixture load.');
    }

    public function testAlbumNamesFixture(): void
    {
        $albums = $this->entityManager->getRepository(Album::class)->findAll();

        $albumNames = array_map(fn($album) => $album->getName(), $albums);

        static::assertContains('Nature', $albumNames);
        static::assertContains('Villes', $albumNames);
        static::assertContains('Nourriture', $albumNames);
        static::assertContains('Animaux', $albumNames);
        static::assertContains('Personnes', $albumNames);
        static::assertContains('Divers', $albumNames);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        if ($this->entityManager->isOpen()) {
            $this->entityManager->close();
        }
    }
}
