<?php

namespace App\Tests\DataFixtures;

use App\DataFixtures\AlbumFixtures;
use App\Entity\Album;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AlbumFixturesTest extends WebTestCase
{
    private $entityManager;

    protected function setUp(): void
    {
        parent::setUp();

        $client = static::createClient();
        $this->entityManager = $client->getContainer()->get('doctrine')->getManager();

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

        $this->assertGreaterThan(0, $albumCount, 'No albums found after fixture load.');
    }

    public function testAlbumNamesFixture(): void
    {
        $albums = $this->entityManager->getRepository(Album::class)->findAll();

        $albumNames = array_map(fn($album) => $album->getName(), $albums);

        $this->assertContains('Nature', $albumNames);
        $this->assertContains('Villes', $albumNames);
        $this->assertContains('Nourriture', $albumNames);
        $this->assertContains('Animaux', $albumNames);
        $this->assertContains('Personnes', $albumNames);
        $this->assertContains('Divers', $albumNames);
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

