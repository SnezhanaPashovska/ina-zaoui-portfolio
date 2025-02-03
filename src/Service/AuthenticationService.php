<?php

namespace App\Service;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class AuthenticationService
{
  private AuthorizationCheckerInterface $authChecker;

  public function __construct(AuthorizationCheckerInterface $authChecker)
  {
    $this->authChecker = $authChecker;
  }
  /**
   * @return array<string, bool> Returns an array with keys 'isAdmin' and 'isAuthenticated' and boolean values.
   */
  public function getAuthenticationData(): array
  {
    $isAdmin = $this->authChecker->isGranted('ROLE_ADMIN');

    $isAuthenticated = $this->authChecker->isGranted('IS_AUTHENTICATED_FULLY');

    return [
      'isAdmin' => $isAdmin,
      'isAuthenticated' => $isAuthenticated,
    ];
  }
}
