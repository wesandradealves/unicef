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
class SameNodeCompanyEditAccessCheck implements AccessInterface {

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

    $user = User::load($account->id());
    if ($account->isAuthenticated() && $user) {
      $roles = $account->getRoles();
      if (in_array('administrator', $roles) ||
          in_array('company_manager', $roles)
      ) {
        return AccessResult::allowed();
      }
      if (in_array('partner_talent_acquisition', $roles)) {
        $bundle = $node->bundle();
        $restrictedBundles = [
          'vacancy',
          'course',
        ];
        if (in_array($bundle, $restrictedBundles)) {
          if ($this->companyService->checkIfNodeCompanyIsEqualUserCompany($user, $node)) {
            return AccessResult::allowedIfHasPermission($account, "edit any $bundle content");
          }
        }
        else {
          return AccessResult::allowedIfHasPermission($account, "edit own $bundle content");
        }
      }
      return AccessResult::forbidden();
    }
    // Other class or services will handle the access.
    return AccessResult::neutral();
  }

}
