<?php

namespace Drupal\company\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Controller to render twig after submit register company manager webform.
 */
class CompanyRegisterController extends ControllerBase {

  /**
   * Display the success page.
   *
   * @return array
   *   Return markup array.
   */
  public function successRegisterWithCommitmentTerm() {

    return [
      '#theme' => 'success_page_company_registered_with_commitment_term',
    ];

  }

  /**
   * Display the success page.
   *
   * @return array
   *   Return markup array.
   */
  public function successRegisterWithoutCommitmentTerm() {

    return [
      '#theme' => 'success_page_company_registered_without_commitment_term',
    ];

  }

}
