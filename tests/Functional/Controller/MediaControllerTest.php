<?php

namespace App\Tests\Controller\Admin;

use App\Entity\Album;
use App\Entity\Media;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\DataFixtures\AppFixturesTest;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class MediaControllerTest extends WebTestCase
{
  private KernelBrowser $client;

  protected function setUp(): void
  {
    parent::setUp();

    $this->client = static::createClient();

    $this->clearMedia();
    $this->clearUsers();

    $this->loadFixtures(
      static::getContainer()->get(UserPasswordHasherInterface::class),
      ['admin', 'guest', 'albums', 'media']
    );

    $entityManager = static::getContainer()->get('doctrine')->getManager();
    $adminUser = $entityManager->getRepository(User::class)->findOneBy(['username' => 'ina_zaoui']);

    if ($adminUser === null) {
      throw new \Exception('Admin user not found.');
    }

    $this->client->loginUser($adminUser);
  }

  private function clearMedia(): void
  {
    $entityManager = static::getContainer()->get('doctrine.orm.entity_manager');
    $medias = $entityManager->getRepository(Media::class)->findAll();

    foreach ($medias as $media) {
      $entityManager->remove($media);
    }
    $entityManager->flush();
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

    $fixture = new AppFixturesTest($passwordHasher, $tags);
    $fixture->load($manager);

    $manager->clear();
  }

  private function ensureTestMediaFileExists(): void
  {
    $testMediaDir = static::getContainer()->getParameter('kernel.project_dir') . '/public/uploads/medias/';
    if (!is_dir($testMediaDir)) {
      mkdir($testMediaDir, 0777, true);
    }

    $testMediaFile = $testMediaDir . 'test_media.jpg';
    if (!file_exists($testMediaFile)) {
      copy(__DIR__ . '/../../Files/test_media.jpg', $testMediaFile);
    }
  }

  public function testIndexAccess(): void
  {
    $this->client->request('GET', '/admin/media');
    static::assertResponseIsSuccessful();
  }

  public function testAddImage(): void
  {
    $tempFile = tmpfile();
    $tempFileMetaData = stream_get_meta_data($tempFile);
    $tempFilePath = $tempFileMetaData['uri'] ?? null;

    if ($tempFilePath === null) {
      static::fail('Temporary file URI could not be determined.');
    }

    file_put_contents($tempFilePath, 'test content');

    $file = new UploadedFile(
      $tempFilePath,
      'test.jpg',
      'image/jpeg',
      null,
      true
    );

    $crawler = $this->client->request('GET', '/admin/media/add');
    static::assertResponseIsSuccessful();

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

    static::assertResponseIsSuccessful();
  }


  public function testDeleteMedia(): void
  {
    $entityManager = static::getContainer()->get('doctrine')->getManager();

    $media = $entityManager->getRepository(Media::class)->findOneBy(['title' => 'Alpes']);
    static::assertNotNull($media, 'No media found in the database.');

    $mediaId = $media->getId();

    $this->ensureTestMediaFileExists();

    $this->client->request('GET', '/admin/media/delete/' . $mediaId);

    static::assertResponseRedirects('/admin/media');

    $deletedMedia = $entityManager->getRepository(Media::class)->find($mediaId);
    static::assertNull($deletedMedia, 'Media was not deleted.');
  }
}
