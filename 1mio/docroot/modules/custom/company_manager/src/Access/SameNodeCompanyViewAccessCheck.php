<?php

namespace Drupal\company_manager\Access;

use Drupal\company\Service\CompanyService;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Check if the user belongs to the same company as the current user.
 */
class SameNodeCompanyViewAccessCheck implements AccessInterface {

  /**
   * The current route.
   *
   * @var \Drupal\Core\Routing\CurrentRouteMatch
   */
  private $currentRouteMatch;

  /**
   * Define the service for the user.
   *
   * @var \Drupal\company\Service\CompanyService
   */
  private $companyService;

  /**
   * The construct method.
   *
   * @param \Drupal\Core\Routing\CurrentRouteMatch $currentRouteMatch
   *   The current route.
   * @param \Drupal\company\Service\CompanyService $companyService
   *   A service for user functions.
   */
  final public function __construct(CurrentRouteMatch $currentRouteMatch, CompanyService $companyService) {
    $this->currentRouteMatch = $currentRouteMatch;
    $this->companyService = $companyService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new static(
      $container->get('current_route_match'),
      $container->get('company.company_service'),
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
    /** @var \Drupal\node\Entity\Node|null $node */
    $node = $this->currentRouteMatch->getParameter('node');
    if (!$node) {
      return AccessResult::neutral();
    }
    if (!$node instanceof Node) {
      $node = Node::load($node);
      if (!$node) {
        return AccessResult::forbidden();
      }
    }
    if ($node->isPublished()) {
      return AccessResult::allowed();
    }

    $user = User::load($account->id());
    if ($account->isAuthenticated() && $user) {
      $roles = $account->getRoles();
      if (in_array('administrator', $roles) ||
          in_array('company_manager', $roles)
      ) {
        return AccessResult::allowed();
      }
      if (in_array('partner_talent_acquisition', $roles)) {
        if ($this->companyService->checkIfNodeCompanyIsEqualUserCompany($user, $node)) {
          $bundle = $node->bundle();
          return AccessResult::allowedIfHasPermissions($account, [
            "view any unpublished content",
            "edit any $bundle content",
          ]);
        }
      }
      return AccessResult::forbidden();
    }

    // Other class or services will handle the access.
    return AccessResult::neutral();
  }

}
