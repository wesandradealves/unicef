<?php

namespace Drupal\umio_user\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Check if the user and the current user is not the same.
 */
class SameLoggedUserAccessCheck implements AccessInterface {
  /**
   * The current route.
   *
   * @var \Drupal\Core\Routing\CurrentRouteMatch
   */
  private $currentRouteMatch;

  /**
   * The construct method.
   *
   * @param \Drupal\Core\Routing\CurrentRouteMatch $currentRouteMatch
   *   The current route.
   */
  final public function __construct(CurrentRouteMatch $currentRouteMatch) {
    $this->currentRouteMatch = $currentRouteMatch;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new static(
      $container->get('current_route_match')
    );
  }

  /**
   * A custom access check.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   Run access checks for this account.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function access(AccountInterface $account) {

    if ($account->isAuthenticated()) {
      $roles = $account->getRoles();
      if (in_array('administrator', $roles)) {
        return AccessResult::allowed();
      }
      if (!in_array('administrator', $roles)) {
        $currentUser = $account->id();
        $accessToUser = $this->currentRouteMatch->getParameter('user');
        if ($accessToUser === $currentUser) {
          return AccessResult::allowed();
        }
      }
    }

    return AccessResult::forbidden();
  }

}
