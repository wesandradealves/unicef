<?php

namespace Drupal\umio_user\Service;

use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;

/**
 * Class to centralize user methods that interact with company node.
 */
class CompanyUserService {

  /**
   * Get the company for the current user.
   *
   * @param \Drupal\user\Entity\User $user
   *   The user to get the company.
   *
   * @return \Drupal\node\Entity\Node|null
   *   Check if the user is in the same company as the current logged user.
   */
  public function getTheCompanyForUser(User $user): ?Node {
    $companyId = $user->get('field_user_company')->getString();
    if (!$companyId) {
      return NULL;
    }

    $company = Node::load($companyId);
    if (!$company) {
      return NULL;
    }

    return $company;
  }

}
