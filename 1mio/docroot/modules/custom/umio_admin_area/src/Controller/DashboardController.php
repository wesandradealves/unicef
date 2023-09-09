<?php

namespace Drupal\umio_admin_area\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\views\Views;

/**
 * DashboardController to show info to company_manager users.
 */
class DashboardController extends ControllerBase {

  /**
   * The id of the dashboard view.
   *
   * @var string
   */
  private const VIEW_ID = 'talent_acquisition_dashboard';

  /**
   * The result key for the content type.
   *
   * @var array
   */
  private const CONTENT_TYPE_RESULT_KEY = [
    'vacancy' => 'vacancies',
    'course'  => 'courses',
  ];

  /**
   * A empty result for the views.
   *
   * @var array
   */
  private const EMPTY_RESULT = [
    self::CONTENT_TYPE_RESULT_KEY['vacancy'] => '0',
    self::CONTENT_TYPE_RESULT_KEY['course'] => '0',
  ];

  /**
   * Render.
   *
   * @return array
   *   Return theme array.
   */
  public function render(): array {
    $vacanciesResults = [];
    $coursesResults = [];
    $results = $this->getPublishedResults();
    if (isset($results[self::CONTENT_TYPE_RESULT_KEY['vacancy']])) {
      $vacanciesResults['published'] = $results[self::CONTENT_TYPE_RESULT_KEY['vacancy']];
    }
    if (isset($results[self::CONTENT_TYPE_RESULT_KEY['course']])) {
      $coursesResults['published'] = $results[self::CONTENT_TYPE_RESULT_KEY['course']];
    }

    $results = $this->getCountViewsResults();
    if (isset($results[self::CONTENT_TYPE_RESULT_KEY['vacancy']])) {
      $vacanciesResults['count'] = $results[self::CONTENT_TYPE_RESULT_KEY['vacancy']];
    }
    if (isset($results[self::CONTENT_TYPE_RESULT_KEY['course']])) {
      $coursesResults['count'] = $results[self::CONTENT_TYPE_RESULT_KEY['course']];
    }

    $vacanciesResults['in_approval'] = $this->getVacancyForApproval();

    return [
      '#theme' => 'content__dashboard_content',
      '#title' => $this->t('Perfomance data'),
      '#vacancies' => $vacanciesResults,
      '#courses' => $coursesResults,
    ];
  }

  /**
   * Function to get an array with all contents published.
   *
   * @return array
   *   An array with the published content type numbers.
   */
  private function getPublishedResults(): array {
    $viewResults = $this->getResultsViewFromDisplay('opportunities_number');

    $results = self::EMPTY_RESULT;
    foreach ($viewResults as $result) {
      if (isset($result->node_field_data_type)) {
        $index = self::CONTENT_TYPE_RESULT_KEY[$result->node_field_data_type];
        $results[$index] = isset($result->nid) ? number_format($result->nid, 0, ',', '.') : '0';
      }
    }

    return $results;
  }

  /**
   * Function to get an array with all view count in content type.
   *
   * @return array
   *   An array with the count of views.
   */
  private function getCountViewsResults(): array {
    $viewResults = $this->getResultsViewFromDisplay('opportunities_count_views');

    $results = self::EMPTY_RESULT;
    foreach ($viewResults as $result) {
      if (isset($result->node_field_data_type)) {
        $index = self::CONTENT_TYPE_RESULT_KEY[$result->node_field_data_type];
        $results[$index] = isset($result->nodeviewcount_uid) ? number_format($result->nodeviewcount_uid, 0, ',', '.') : '0';
      }
    }

    return $results;
  }

  /**
   * Function to get the number of vacancy for approval.
   *
   * @return string
   *   Number of vacancies for approval.
   */
  private function getVacancyForApproval(): string {
    $results = $this->getResultsViewFromDisplay('vacancy_for_approval');
    if (isset($results[0])) {
      $result = $results[0];

      return isset($result->nid) ? number_format($result->nid, 0, ',', '.') : '0';
    }

    return '0';
  }

  /**
   * Get the results from the display of the view.
   *
   * @return array
   *   Return the results from the display of the view.
   */
  private function getResultsViewFromDisplay(string $display): array {
    $view = Views::getView(self::VIEW_ID);
    $view->setDisplay($display);
    $view->execute();

    return $view->result;
  }

}
