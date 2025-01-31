<?php

namespace App\Service;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class AuthenticationService
{
  private $authChecker;

  public function __construct(AuthorizationCheckerInterface $authChecker)
  {
    $this->authChecker = $authChecker;
  }

  public function getAuthenticationData()
  {
    $isAdmin = $this->authChecker->isGranted('ROLE_ADMIN');

    $isAuthenticated = $this->authChecker->isGranted('IS_AUTHENTICATED_FULLY');

    return [
      'isAdmin' => $isAdmin,
      'isAuthenticated' => $isAuthenticated,
    ];
  }
}
