<?php

namespace Drupal\company\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\umio_user\Service\UserService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Check if the user belongs to the same company as the current user.
 */
class BranchAccessCheck implements AccessInterface {

  /**
   * The current route.
   *
   * @var \Drupal\Core\Routing\CurrentRouteMatch
   */
  private $currentRouteMatch;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * Define the service for the user.
   *
   * @var \Drupal\umio_user\Service\UserService
   */
  private $userService;

  /**
   * The construct method.
   *
   * @param \Drupal\Core\Routing\CurrentRouteMatch $currentRouteMatch
   *   The current route.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\umio_user\Service\UserService $userService
   *   A service for user functions.
   */
  final public function __construct(
    CurrentRouteMatch $currentRouteMatch,
    EntityTypeManagerInterface $entityTypeManager,
    UserService $userService
  ) {
    $this->currentRouteMatch = $currentRouteMatch;
    $this->entityTypeManager = $entityTypeManager;
    $this->userService = $userService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new static(
      $container->get('current_route_match'),
      $container->get('entity_type.manager'),
      $container->get('umio_user.user_service'),
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
      if (in_array('partner_talent_acquisition', $roles)) {
        $company = $this->currentRouteMatch->getParameter('company');
        if (!is_numeric($company)) {
          return AccessResult::allowed();
        }
        $companies = $this->userService->getOnlyBranchsCompaniesForTheCurrentUser();
        if ($companies && in_array($company, $companies)) {
          return AccessResult::allowed();
        }
      }
    }
    return AccessResult::forbidden();
  }

}
