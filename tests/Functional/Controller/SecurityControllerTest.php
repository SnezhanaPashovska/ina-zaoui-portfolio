<?php

namespace App\Tests\Controller\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class SecurityControllerTest extends WebTestCase
{
  public function testLoginPageLoads(): void
  {
    $client = static::createClient();
    $crawler = $client->request('GET', '/login');

    $this->assertResponseIsSuccessful();
    $this->assertSelectorTextContains('h1', 'Connexion');
  }

  public function testSuccessfulLogin(): void
  {
    $client = static::createClient();
    $container = self::getContainer();
    $entityManager = $container->get('doctrine')->getManager();
    $passwordHasher = $container->get(UserPasswordHasherInterface::class);

    $user = new User();
    $user->setUsername('testuser');
    $user->setEmail('test@example.com');
    $user->setRoles(['ROLE_ADMIN']);
    $user->setPassword($passwordHasher->hashPassword($user, 'password123'));

    $entityManager->persist($user);
    $entityManager->flush();

    $crawler = $client->request('GET', '/login');
    $form = $crawler->selectButton('Connexion')->form([
      '_username' => 'testuser',
      '_password' => 'password123',
    ]);

    $client->submit($form);

    $this->assertResponseRedirects('/');
    $client->followRedirect();
    $this->assertSelectorTextContains('h2', 'Photographe');
  }

  public function testInvalidLogin(): void
  {
    $client = static::createClient();

    $crawler = $client->request('GET', '/login');
    $form = $crawler->selectButton('Connexion')->form([
      '_username' => 'invaliduser',
      '_password' => 'wrongpassword',
    ]);

    $client->submit($form);
    $crawler = $client->followRedirect();

    $this->assertSelectorExists('.alert.alert-danger');
  }
}
