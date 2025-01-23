<?php

namespace App\Tests\Unit\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
  public function testUserEntity()
  {
    $user = new User();

    $this->assertNull($user->getId()); 

    $user->setUsername('john_doe');
    $this->assertEquals('john_doe', $user->getUsername());

    $user->setName('John Doe');
    $this->assertEquals('John Doe', $user->getName());

    $user->setEmail('john.doe@example.com');
    $this->assertEquals('john.doe@example.com', $user->getEmail());

    $user->setDescription('A brief description of John');
    $this->assertEquals('A brief description of John', $user->getDescription());

    $user->setRoles(['ROLE_USER', 'ROLE_ADMIN']);
    $this->assertEquals(['ROLE_USER', 'ROLE_ADMIN'], $user->getRoles());

    $user->setPassword('$2y$04$IeNqTlzSlxK8rUvHsBklkuJzn6b9rW0JulJRvil/TKBHt9TxOnPIS');
    $this->assertEquals('$2y$04$IeNqTlzSlxK8rUvHsBklkuJzn6b9rW0JulJRvil/TKBHt9TxOnPIS', $user->getPassword());

    $user->setIsActive(true);
    $this->assertTrue($user->isActive());

    $user->setAdmin(true);
    $this->assertTrue($user->isAdmin());
  }
}
