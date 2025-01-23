<?php

namespace App\Tests\Controller\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


class SecurityControllerTest extends WebTestCase
{
  private $client = null;

  protected function setUp(): void
  {
    parent::setUp();
    $this->client = static::createClient();

    $this->clearUsers();
    $this->loadFixtures(
      $this->getContainer()->get(UserPasswordHasherInterface::class),
      ['admin', 'guest']
    );
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

    $fixture = new \App\DataFixtures\AppFixturesTest($passwordHasher, $tags);

    $fixture->load($manager);
    $manager->flush();
  }

  public function testLoginPageLoads(): void
  {
    $crawler = $this->client->request('GET', '/login');

    $this->assertResponseIsSuccessful();
    $this->assertSelectorTextContains('h1', 'Connexion');
  }

  public function testSuccessfulLogin(): void
  {
    $crawler = $this->client->request('GET', '/login');
    $form = $crawler->selectButton('Connexion')->form([
      '_username' => 'guestUser',
      '_password' => 'guestpassword',
    ]);

    $this->client->submit($form);

    $this->assertResponseRedirects('/');
    $this->client->followRedirect();
    $this->assertSelectorTextContains('h2', 'Photographe');
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

    $this->assertSelectorExists('.alert.alert-danger');
  }
}
