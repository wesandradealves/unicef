<?php

namespace Drupal\company\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\umio_user\Service\CompanyUserService;
use Drupal\umio_user\UserStatusTrait;
use Drupal\user\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Check if the user belongs to the same company as the current user.
 */
class OnlyCompanyAccessCheck implements AccessInterface {

  use UserStatusTrait;

  /**
   * The current route.
   *
   * @var \Drupal\umio_user\Service\CompanyUserService
   */
  private $companyUserService;

  /**
   * The construct method.
   *
   * @param \Drupal\umio_user\Service\CompanyUserService $companyUserService
   *   The company user service.
   */
  final public function __construct(CompanyUserService $companyUserService) {
    $this->companyUserService = $companyUserService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new static(
      $container->get('umio_user.company_service'),
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
    $user = User::load($account->id());
    $status = $this->getUserStatus($user);
    if (!$user || $status !== $this->getActiveStatus()) {
      return AccessResult::forbidden();
    }

    $company = $this->companyUserService->getTheCompanyForUser($user);
    $type = $company->get('field_company_type')->getString();

    if ($type === 'company') {
      return AccessResult::allowed();
    }

    return AccessResult::forbidden();
  }

}
