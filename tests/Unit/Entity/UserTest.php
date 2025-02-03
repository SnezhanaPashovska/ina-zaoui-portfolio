<?php

namespace App\Tests\Unit\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
  public function testUserEntity(): void
  {
    $user = new User();

    static::assertNull($user->getId());

    $user->setUsername('john_doe');
    static::assertEquals('john_doe', $user->getUsername());

    $user->setName('John Doe');
    static::assertEquals('John Doe', $user->getName());

    $user->setEmail('john.doe@example.com');
    static::assertEquals('john.doe@example.com', $user->getEmail());

    $user->setDescription('A brief description of John');
    static::assertEquals('A brief description of John', $user->getDescription());

    $user->setRoles(['ROLE_USER', 'ROLE_ADMIN']);
    static::assertEquals(['ROLE_USER', 'ROLE_ADMIN'], $user->getRoles());

    $user->setPassword('$2y$04$IeNqTlzSlxK8rUvHsBklkuJzn6b9rW0JulJRvil/TKBHt9TxOnPIS');
    static::assertEquals('$2y$04$IeNqTlzSlxK8rUvHsBklkuJzn6b9rW0JulJRvil/TKBHt9TxOnPIS', $user->getPassword());

    $user->setIsActive(true);
    static::assertTrue($user->isActive());

    $user->setAdmin(true);
    static::assertTrue($user->isAdmin());
  }
}
