<?php

namespace Drupal\company\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Service with helpers function to handle with company content type.
 */
class CompanyService {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * The construct method.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   */
  final public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new static(
      $container->get('entity_type.manager'),
    );
  }

  /**
   * Get all the companies and branches for the current logged user.
   *
   * @return int|null
   *   Return an array (nid) with all companies on the organization structure.
   */
  public function getMainBranchFromCompany(Node $company): ?int {
    $isMainOffice = $company->get('field_company_main_office')->getString() ? FALSE : TRUE;
    if (!$isMainOffice) {
      $mainOfficeId = $company->get('field_company_main_office')->getString();
    }
    else {
      $mainOfficeId = $company->id();
    }

    return $mainOfficeId;
  }

  /**
   * Get all the companies and branches for the current logged user.
   *
   * @return array
   *   Return an array (nid) with all companies on the organization structure.
   */
  public function getAllBranchsFromCompany(Node $company): array {
    $mainOfficeId = $this->getMainBranchFromCompany($company);

    $companies = $this->entityTypeManager
      ->getStorage('node')
      ->getQuery()
      ->condition('type', 'company')
      ->condition('field_company_main_office', $mainOfficeId)
      ->execute();

    $companyBranches = [
      $mainOfficeId,
    ];
    foreach ($companies as $companyId) {
      $companyBranches[] = $companyId;
    }

    return $companyBranches;
  }

  /**
   * Get only the branches for company passed as parameter.
   *
   * @return array
   *   Return an array (nid) with all branches from the organization.
   */
  public function getOnlyBranchesFromCompany(Node $company): array {
    $mainOfficeId = $this->getMainBranchFromCompany($company);
    $companies = $this->entityTypeManager
      ->getStorage('node')
      ->getQuery()
      ->condition('type', 'company')
      ->condition('field_company_main_office', $mainOfficeId)
      ->execute();

    $companyBranches = [];
    foreach ($companies as $companyId) {
      $companyBranches[] = $companyId;
    }
    return $companyBranches;
  }

  /**
   * Return the active companies.
   *
   * @return array
   *   Array with published companies.
   */
  public function getActiveCompanies() : array {
    $nodes = $this->entityTypeManager
      ->getStorage('node')
      ->getQuery()
      ->condition('field_company_type', 'company')
      ->condition('status', '1')
      ->sort('title', 'SORT_ASC')
      ->execute();

    $nodes = $this->entityTypeManager
      ->getStorage('node')
      ->loadMultiple($nodes);

    $companies = [];
    /** @var \Drupal\node\Entity\Node $node */
    foreach ($nodes as $nid => $node) {
      $companies[$nid] = $node->get('title')->getValue()[0]['value'];
    }

    return $companies;
  }

  /**
   * Return the active company and civil-society.
   *
   * @return array
   *   Array with published companies.
   */
  public function getActiveCompaniesandSocieties() : array {
    $query = $this->entityTypeManager
      ->getStorage('node')
      ->getQuery();
    $conditionGroup = $query
      ->orConditionGroup()
      ->condition('field_company_type', 'company')
      ->condition('field_company_type', 'civil-society');
    $nodes = $query
      ->condition($conditionGroup)
      ->condition('status', '1')
      ->sort('title', 'SORT_ASC')
      ->execute();

    $nodes = $this->entityTypeManager
      ->getStorage('node')
      ->loadMultiple($nodes);

    $companies = [];
    /** @var \Drupal\node\Entity\Node $node */
    foreach ($nodes as $nid => $node) {
      $companies[$nid] = $node->get('title')->getValue()[0]['value'];
    }

    return $companies;
  }

  /**
   * Verify if the user is in the same company as the node.
   *
   * @param \Drupal\user\Entity\User $user
   *   The user entity.
   * @param \Drupal\node\Entity\Node $node
   *   Node with company field.
   */
  public function checkIfNodeCompanyIsEqualUserCompany(User $user, Node $node): bool {
    $nodeCompanyId = $this->getCompanyIdByNode($node);
    if ($nodeCompanyId) {
      $userCompanyId = $user->get('field_user_company')->getString();
      if (!$userCompanyId) {
        return FALSE;
      }

      $company = Node::load($nodeCompanyId);
      if (!$company) {
        return FALSE;
      }
      $companies = $this->getAllBranchsFromCompany($company);
      if (in_array($userCompanyId, $companies)) {
        return TRUE;
      }
    }

    return FALSE;
  }

  /**
   * Get the company id from node based on the bundle of the node.
   *
   * @param \Drupal\node\Entity\Node $node
   *   The current node.
   *
   * @return string|null
   *   The company id of the node.
   */
  public function getCompanyIdByNode(Node $node): ?string {
    $bundle = $node->bundle();
    switch ($bundle) {
      case 'company':
        return (string) $node->id();

      case 'vacancy':
        return $node->get('field_vacancy_company')->getString();

      case 'course':
        return $node->get('field_course_institution')->getString();
    }

    return NULL;
  }

}
