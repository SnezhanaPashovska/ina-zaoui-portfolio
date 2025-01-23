<?php

namespace App\Tests\Controller\Functional;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\DataFixtures\AppFixturesTest;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Album;

class AlbumControllerTest extends WebTestCase
{
    private $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();

        $this->clearUsers();

        $this->loadFixtures(
            $this->getContainer()->get(UserPasswordHasherInterface::class),
            ['admin', 'albums']
        );

        $entityManager = $this->client->getContainer()->get('doctrine')->getManager();
        $adminUser = $entityManager->getRepository(User::class)->findOneBy(['username' => 'ina_zaoui']);
        $this->client->loginUser($adminUser);
    }

    private function clearUsers(): void
    {
        $entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');
        $users = $entityManager->getRepository(User::class)->findAll();

        foreach ($users as $user) {
            $entityManager->remove($user);
        }
        $entityManager->flush();
    }

    private function loadFixtures(UserPasswordHasherInterface $passwordHasher, array $tags): void
    {
        $manager = self::getContainer()->get('doctrine')->getManager();

        $fixture = new AppFixturesTest($passwordHasher, $tags);
        $fixture->load($manager);

        $manager->clear();
    }

    public function testAdminCanAccessAlbumIndex(): void
    {
        $this->client->request('GET', '/admin/album');

        $this->assertResponseIsSuccessful();
    }

    public function testAdminCanAddAlbum(): void
    {
        $crawler = $this->client->request('GET', '/admin/album/add');
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Ajouter')->form([
            'album[name]' => 'New Test Album',
        ]);
        $this->client->submit($form);

        $this->assertResponseRedirects('/admin/album');
        $this->client->followRedirect();

        $this->assertSelectorTextContains('h1', 'Admin');
    }

    public function testAdminCanUpdateAlbum(): void
    {
        $entityManager = $this->client->getContainer()->get('doctrine')->getManager();

        $album = $entityManager->getRepository(Album::class)->findOneBy(['name' => 'Nature']);

        $crawler = $this->client->request('GET', '/admin/album/update/' . $album->getId());
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Modifier')->form([
            'album[name]' => 'Updated Album Name',
        ]);
        $this->client->submit($form);

        $this->assertResponseRedirects('/admin/album');

        $updatedAlbum = $entityManager->getRepository(Album::class)->find($album->getId());
        $this->assertSame('Updated Album Name', $updatedAlbum->getName());
    }

    public function testAdminCanDeleteAlbum(): void
    {
        $entityManager = $this->client->getContainer()->get('doctrine')->getManager();

        $album = $entityManager->getRepository(Album::class)->findOneBy(['name' => 'Nature']);

        $this->assertNotNull($album, 'No album found in the database.');

        $albumId = $album->getId();

        $this->client->request('GET', '/admin/album/delete/' . $albumId);

        $this->assertResponseRedirects('/admin/album');

        $deletedAlbum = $entityManager->getRepository(Album::class)->find($albumId);
        $this->assertNull($deletedAlbum);
    }
}
