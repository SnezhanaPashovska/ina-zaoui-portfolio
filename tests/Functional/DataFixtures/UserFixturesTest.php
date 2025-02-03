<?php

namespace App\Tests\DataFixtures;

use App\DataFixtures\UserFixtures;
use App\Entity\User;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;

class UserFixturesTest extends WebTestCase
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

        $loader->addFixture(static::getContainer()->get(UserFixtures::class));

        $purger = new ORMPurger($this->entityManager);
        $executor = new ORMExecutor($this->entityManager, $purger);
        $executor->execute($loader->getFixtures());
    }

    public function testUserFixtures(): void
    {
        $userCount = $this->entityManager->getRepository(User::class)->count([]);
        static::assertGreaterThan(0, $userCount, 'No users found after fixture load.');
    }
    protected function tearDown(): void
    {
        parent::tearDown();

        if ($this->entityManager->isOpen()) {
            $this->entityManager->close();
        }
    }
}
