<?php

namespace Drupal\umio_user\Service;

use Drupal\company\Service\CompanyService;
use Drupal\Core\Session\AccountInterface;
use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class to centralize user methods.
 */
class UserService {

  /**
   * The company helper service.
   *
   * @var \Drupal\company\Service\CompanyService
   */
  private $companyService;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  private $user;

  /**
   * The construct method.
   *
   * @param \Drupal\company\Service\CompanyService $companyService
   *   The company helper service.
   * @param \Drupal\Core\Session\AccountInterface $user
   *   The current user.
   */
  final public function __construct(CompanyService $companyService, AccountInterface $user) {
    $this->companyService = $companyService;
    $this->user = $user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): UserService {
    return new static(
      $container->get('company.company_service'),
      $container->get('current_user'),
    );
  }

  /**
   * Check if the user is in the same company as the current user.
   *
   * @param \Drupal\user\Entity\User $user
   *   The user entity.
   *
   * @return bool
   *   Check if the user is in the same company as the current logged user.
   */
  public function checkIfSameCompanyAsTheCurrentUser(User $user): bool {
    $companyId = $user->get('field_user_company')->getString();
    if (!$companyId) {
      return FALSE;
    }

    $companies = $this->getAllBranchCompaniesForTheCurrentUser();
    if ($companies && in_array($companyId, $companies)) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * Get all the companies and branchs for the current logged user.
   *
   * @return array|null
   *   Return an array (nid) with all companies on the organization structure.
   */
  public function getAllBranchCompaniesForTheCurrentUser(): ?array {
    $user = User::load($this->user->id());
    return $this->getAllBranchCompaniesForTheUser($user);
  }

  /**
   * Get all the companies and branchs for the user.
   *
   * @param \Drupal\user\Entity\User $user
   *   The user entity.
   *
   * @return array|null
   *   Return an array (nid) with all companies on the organization structure.
   */
  public function getAllBranchCompaniesForTheUser(User $user): ?array {
    $userCompanyId = $user->get('field_user_company')->getString();
    if ($userCompanyId) {
      $company = Node::load($userCompanyId);
      return $this->companyService->getAllBranchsFromCompany($company);
    }

    return NULL;
  }

  /**
   * Get all the companies and branchs for the current logged user.
   *
   * @return int|null
   *   Return an array (nid) with all companies on the organization structure.
   */
  public function getCurrentUserMainCompany(): ?int {
    $user = User::load($this->user->id());
    $userCompanyId = $user->get('field_user_company')->getString();
    if ($userCompanyId) {
      $company = Node::load($userCompanyId);
      return $this->companyService->getMainBranchFromCompany($company);
    }
    return NULL;
  }

  /**
   * Get all the companies and branchs for the current logged user.
   *
   * @return array|null
   *   Return an array (nid) with all companies on the organization structure.
   */
  public function getOnlyBranchsCompaniesForTheCurrentUser(): ?array {
    $user = User::load($this->user->id());
    $userCompanyId = $user->get('field_user_company')->getString();
    if ($userCompanyId) {
      $company = Node::load($userCompanyId);
      return $this->companyService->getOnlyBranchesFromCompany($company);
    }
    return NULL;
  }

  /**
   * Get user id data from the logged user.
   *
   * @return int
   *   Return a integer value with the user id.
   */
  public function userId(): int {
    return $this->user->id();
  }

}
