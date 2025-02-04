<?php

namespace App\Tests\Functional\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use App\Tests\AppFixturesTest;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Album;
use App\Tests\AppFixturesTest as TestsAppFixturesTest;

class AlbumControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();

        $this->clearUsers();

        $this->loadFixtures(
            static::getContainer()->get(UserPasswordHasherInterface::class),
            ['admin', 'albums']
        );

        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $adminUser = $entityManager->getRepository(User::class)->findOneBy(['username' => 'ina_zaoui']);

        if ($adminUser === null) {
            throw new \Exception('Admin user not found');
        }

        $this->client->loginUser($adminUser);
    }

    private function clearUsers(): void
    {
        $entityManager = static::getContainer()->get('doctrine.orm.entity_manager');
        $users = $entityManager->getRepository(User::class)->findAll();

        foreach ($users as $user) {
            $entityManager->remove($user);
        }
        $entityManager->flush();
    }

    /**
     * Load the fixtures for the test.
     * 
     * @param UserPasswordHasherInterface $passwordHasher
     * @param array<string> $tags
     */

    private function loadFixtures(UserPasswordHasherInterface $passwordHasher, array $tags): void
    {
        $manager = static::getContainer()->get('doctrine')->getManager();

        $fixture = new TestsAppFixturesTest($passwordHasher, $tags);
        $fixture->load($manager);

        $manager->clear();
    }

    public function testAdminCanAccessAlbumIndex(): void
    {
        $this->client->request('GET', '/admin/album');

        static::assertResponseIsSuccessful();
    }

    public function testAdminCanAddAlbum(): void
    {
        $crawler = $this->client->request('GET', '/admin/album/add');
        static::assertResponseIsSuccessful();

        $form = $crawler->selectButton('Ajouter')->form([
            'album[name]' => 'New Test Album',
        ]);
        $this->client->submit($form);

        static::assertResponseRedirects('/admin/album');
        $this->client->followRedirect();

        static::assertSelectorTextContains('h1', 'Admin');
    }

    public function testAdminCanUpdateAlbum(): void
    {
        $entityManager = $this->client->getContainer()->get('doctrine')->getManager();

        $album = $entityManager->getRepository(Album::class)->findOneBy(['name' => 'Nature']);
        static::assertNotNull($album, 'No album found in the database.');

        $crawler = $this->client->request('GET', '/admin/album/update/' . $album->getId());
        static::assertResponseIsSuccessful();

        $form = $crawler->selectButton('Modifier')->form([
            'album[name]' => 'Updated Album Name',
        ]);
        $this->client->submit($form);

        static::assertResponseRedirects('/admin/album');

        $updatedAlbum = $entityManager->getRepository(Album::class)->find($album->getId());
        static::assertNotNull($updatedAlbum, 'Updated album not found in the database.');
        static::assertSame('Updated Album Name', $updatedAlbum->getName());
    }

    public function testAdminCanDeleteAlbum(): void
    {
        $entityManager = $this->client->getContainer()->get('doctrine')->getManager();

        $album = $entityManager->getRepository(Album::class)->findOneBy(['name' => 'Nature']);
        static::assertNotNull($album, 'No album found in the database.');

        $albumId = $album->getId();

        $this->client->request('GET', '/admin/album/delete/' . $albumId);

        static::assertResponseRedirects('/admin/album');

        $deletedAlbum = $entityManager->getRepository(Album::class)->find($albumId);
        static::assertNull($deletedAlbum);
    }
}
