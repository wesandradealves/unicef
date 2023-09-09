<?php

namespace Drupal\umio_vacancy\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Returns responses for vacancy routes.
 */
class VacancyController extends ControllerBase {

  /**
   * Display the success page with a link to register company page.
   *
   * @return array
   *   Return markup array.
   */
  public function index() {
    return [
      '#theme' => 'page_umio_vacancy_create',
      '#attached' => [
        'library' => [
          'umio_vacancy/umio_vacancy.index_page',
        ],
      ],
    ];
  }

}
