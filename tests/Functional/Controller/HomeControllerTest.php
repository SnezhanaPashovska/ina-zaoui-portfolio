<?php

namespace App\Tests\Controller\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\User;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class HomeControllerTest extends WebTestCase
{

  private function createUser()
  {
    $user = new User();
    $user->setName('Test Guest');
    $user->setUsername('Test Guest');
    $user->setAdmin(false);
    $user->setIsActive(true);
    $user->setDescription('Test description');
    $user->setEmail('testguest@example.com');
    $user->setPassword('password123');

    $entityManager = self::getContainer()->get('doctrine')->getManager();

    $entityManager->persist($user);
    $entityManager->flush();

    return $user;
  }

  private function createAdminUser()
  {
    $user = new User();
    $user->setName('Ina Zaoui');
    $user->setUsername('ina_zaoui');
    $user->setPassword('password123');
    $user->setIsActive(true);
    $user->setDescription('Test description');
    $user->setAdmin(true);
    $user->setEmail('ina@zaoui.com');
    $user->setRoles(['ROLE_ADMIN']);

    $entityManager = self::getContainer()->get('doctrine')->getManager();
    $entityManager->persist($user);
    $entityManager->flush();

    return $user;
  }

  public function testHomePageLoadsSuccessfully(): void
  {
    $client = static::createClient();
    $client->request('GET', '/');

    $this->assertResponseIsSuccessful();
    $this->assertSelectorExists('nav');
  }

  public function testGuestsPageLoadsSuccessfully(): void
  {
    $client = static::createClient();
    $client->request('GET', '/guests');

    $this->assertResponseIsSuccessful();
    $this->assertSelectorTextContains('h3', 'Invités');
  }

  public function testGuestPageWithValidId(): void
  {
    $client = static::createClient();

    $user = $this->createUser();
    $client->request('GET', '/guest/' . $user->getId());

    $this->assertResponseIsSuccessful();
    $this->assertSelectorExists('h3', 'guest.name');
  }


  public function testPortfolioPageLoadsSuccessfully(): void
  {
    $client = static::createClient();
    $client->request('GET', '/portfolio');

    $this->assertResponseIsSuccessful();
    $this->assertSelectorTextContains('h3', 'Portfolio');
  }

  public function testAboutPageLoadsSuccessfully(): void
  {
    $client = static::createClient();
    $client->request('GET', '/about');

    $this->assertResponseIsSuccessful();
    $this->assertSelectorTextContains('h2', 'Qui suis-je ?');
  }

  public function testAdminPageLoadsSuccessfully(): void
  {
    $client = static::createClient();

    $adminUser = $this->createAdminUser();

    $client->loginUser($adminUser);

    $client->request('GET', '/admin');

    $this->assertResponseIsSuccessful();
    $this->assertSelectorTextContains('h1', 'Admin');
  }
}
