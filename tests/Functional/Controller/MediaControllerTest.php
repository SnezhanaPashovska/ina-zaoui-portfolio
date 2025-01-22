<?php

namespace App\Tests\Controller\Admin;

use App\Entity\Album;
use App\Entity\Media;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class MediaControllerTest extends WebTestCase
{
  private KernelBrowser $client;


  protected function setUp(): void
  {
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

    $album = $entityManager->getRepository(Album::class)->findOneBy(['name' => 'Divers']);
    if (!$album) {
      $album = new Album();
      $album->setName('Divers');
      $entityManager->persist($album);
      $entityManager->flush();
    }

    $media = $entityManager->getRepository(Media::class)->findOneBy(['title' => 'Alpes']);
    if (!$media) {
      $tempFilePath = sys_get_temp_dir() . '/test.jpg';
      file_put_contents($tempFilePath, 'Temporary file content');

      $media = new Media();
      $media->setTitle('Alpes');
      $media->setPath($tempFilePath);
      $media->setUser($adminUser);
      $media->setAlbum($album);
      $entityManager->persist($media);
      $entityManager->flush();
    }
  }

  public function testIndexAccess(): void
  {
    $this->client->request('GET', '/admin/media');
    $this->assertResponseIsSuccessful();
  }


  public function testAddImage(): void
  {
    $file = new UploadedFile(
      sys_get_temp_dir() . '/test.jpg',
      'test.jpg',
      'image/jpeg',
      null,
      true
    );

    $crawler = $this->client->request('GET', '/admin/media/add');
    $this->assertResponseIsSuccessful();

    $this->client->request(
      'POST',
      '/admin/media/add',
      [
        'media[title]' => 'test.jpg',
        'media[user]' => 1,
        'media[album]' => 1,
      ],
      [
        'media[file]' => $file
      ],
      ['CONTENT_TYPE' => 'multipart/form-data']
    );

    $this->assertResponseIsSuccessful();
  }


  public function testDeleteMedia(): void
  {
    $entityManager = $this->client->getContainer()->get('doctrine')->getManager();

    $media = $entityManager->getRepository(Media::class)->findOneBy(['title' => 'Alpes']);

    $this->assertNotNull($media, 'No media found in the database.');

    $mediaId = $media->getId();

    $this->client->request('GET', '/admin/media/delete/' . $mediaId);

    $this->assertResponseRedirects('/admin/media');

    $deletedMedia = $entityManager->getRepository(Media::class)->find($mediaId);
    $this->assertNull($deletedMedia, 'Media was not deleted.');
  }
}
