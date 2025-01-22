<?php

namespace App\Tests\Controller\Functional;

use App\Entity\Album;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AlbumControllerTest extends WebTestCase
{
    private $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();

        $entityManager = $this->client->getContainer()->get('doctrine')->getManager();
        $adminUser = $entityManager->getRepository(User::class)->findOneBy(['username' => 'ina_zaoui']);

        if (!$adminUser) {
            $adminUser = new User();
            $adminUser->setName('Ina Zaoui');
            $adminUser->setUsername('ina_zaoui');
            $adminUser->setPassword('password');
            $adminUser->setEmail('ina_zaoui@example.com');
            $adminUser->setRoles(['ROLE_ADMIN']);
            $entityManager->persist($adminUser);
            $entityManager->flush();
        }
        $this->client->loginUser($adminUser);

        $album = new Album();
        $album->setName('Test Album');
        $entityManager->persist($album);
        $entityManager->flush();
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
            'album[name]' => 'Test Album',
        ]);
        $this->client->submit($form);

        $this->assertResponseRedirects('/admin/album');
        $this->client->followRedirect();

        $this->assertSelectorTextContains('h1', 'Admin');
    }

    public function testAdminCanUpdateAlbum(): void
    {
        $entityManager = $this->client->getContainer()->get('doctrine')->getManager();

        $album = new Album();
        $album->setName('Initial Name');
        $entityManager->persist($album);
        $entityManager->flush();

        $crawler = $this->client->request('GET', '/admin/album/update/' . $album->getId());
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Modifier')->form([
            'album[name]' => 'Updated Name',
        ]);
        $this->client->submit($form);

        $this->assertResponseRedirects('/admin/album');

        $updatedAlbum = $entityManager->getRepository(Album::class)->find($album->getId());
        $this->assertSame('Updated Name', $updatedAlbum->getName());
    }

    public function testAdminCanDeleteAlbum(): void
    {
        $entityManager = $this->client->getContainer()->get('doctrine')->getManager();

        $album = $entityManager->getRepository(Album::class)->findOneBy(['name' => 'Test Album']);

        $this->assertNotNull($album, 'No album found in the database.');

        $albumId = $album->getId();

        $this->client->request('GET', '/admin/album/delete/' . $albumId);

        $this->assertResponseRedirects('/admin/album');

        $deletedAlbum = $entityManager->getRepository(Album::class)->find($albumId);
        $this->assertNull($deletedAlbum);
    }
}
