<?php

namespace App\Tests\Controller\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


class SecurityControllerTest extends WebTestCase
{
  private KernelBrowser $client;

  protected function setUp(): void
  {
    parent::setUp();
    $this->client = static::createClient();

    $this->clearUsers();
    $this->loadFixtures(
      static::getContainer()->get(UserPasswordHasherInterface::class),
      ['admin', 'guest']
    );
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

    $fixture = new \App\DataFixtures\AppFixturesTest($passwordHasher, $tags);
    $fixture->load($manager);

    $manager->flush();
  }

  public function testLoginPageLoads(): void
  {
    $crawler = $this->client->request('GET', '/login');

    static::assertResponseIsSuccessful();
    static::assertSelectorTextContains('h1', 'Connexion');
  }

  public function testSuccessfulLogin(): void
  {
    $crawler = $this->client->request('GET', '/login');
    $form = $crawler->selectButton('Connexion')->form([
      '_username' => 'guestUser',
      '_password' => 'guestpassword',
    ]);

    $this->client->submit($form);

    static::assertResponseRedirects('/');
    $this->client->followRedirect();
    static::assertSelectorTextContains('h2', 'Photographe');
  }

  public function testInvalidLogin(): void
  {
    $crawler = $this->client->request('GET', '/login');
    $form = $crawler->selectButton('Connexion')->form([
      '_username' => 'invaliduser',
      '_password' => 'wrongpassword',
    ]);

    $this->client->submit($form);
    $crawler = $this->client->followRedirect();

    static::assertSelectorExists('.alert.alert-danger');
  }
}
