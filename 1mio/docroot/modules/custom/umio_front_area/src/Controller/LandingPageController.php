<?php

namespace Drupal\umio_front_area\Controller;

use Drupal\Component\Render\MarkupInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\Renderer;
use Drupal\Core\Url;
use Drupal\views\ViewExecutable;
use Drupal\views\Views;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Returns responses for 1mio Front Area routes.
 */
class LandingPageController extends ControllerBase {

  /**
   * Define the renderer.
   *
   * @var \Drupal\Core\Render\Renderer
   */
  private $renderer;

  /**
   * Define the constructor.
   *
   * @param \Drupal\Core\Render\Renderer $renderer
   *   The core Drupal service renderer.
   */
  final public function __construct(Renderer $renderer) {
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) : self {
    return new static(
      $container->get('renderer'),
    );
  }

  /**
   * Builds the response.
   *
   * @return array
   *   The render array.
   */
  public function build(): array {
    $logged_in = \Drupal::currentUser()->isAuthenticated();
    if ($logged_in) {
      $path = Url::fromRoute('view.social_feed.social_feed_display')->toString();
      $response = new RedirectResponse($path);
      $response->send();
    }

    $viewJobTotal = $this->executeView('umio_counters', 'job_total');
    $jobsTotal = $this->getFieldInsideView($viewJobTotal);
    $jobsTotal += $this->getConfigField('field_counter_job_total');
    $jobsTotal = number_format($jobsTotal, 0, ',', '.');

    $viewVacanciesClosed = $this->executeView('umio_user_counters', 'vacancies_closed');
    $vacanciesClosed = $this->getFieldInsideView($viewVacanciesClosed);
    $vacanciesClosed += $this->getConfigField('field_counter_vacancies_closed');
    $vacanciesClosed = number_format($vacanciesClosed, 0, ',', '.');

    $viewVacanciesTotal = $this->executeView('umio_counters', 'vacancies_total');
    $vacanciesTotal = $this->getFieldInsideView($viewVacanciesTotal);
    $vacanciesTotal += $this->getConfigField('field_counter_vacancies_total');
    $vacanciesTotal = number_format($vacanciesTotal, 0, ',', '.');

    $viewCoursesTotal = $this->executeView('umio_counters', 'courses_total');
    $coursesTotal = $this->getFieldInsideView($viewCoursesTotal);
    $coursesTotal += $this->getConfigField('field_counter_courses_total');
    $coursesTotal = number_format($coursesTotal, 0, ',', '.');

    $connectivityInclusion = number_format($this->getConfigField('field_counter_connectivity_inclusion'), 0, ',', '.');

    return [
      '#theme' => 'page_landing_page_anonymous',
      '#job_total' => $jobsTotal,
      '#vacancies_total' => $vacanciesTotal,
      '#courses_total' => $coursesTotal,
      '#vacancies_closed' => $vacanciesClosed,
      '#connectivity_inclusion' => $connectivityInclusion,
    ];
  }

  /**
   * Returns the view rendered.
   *
   * @param string $viewMachineName
   *   The machine name of the view.
   * @param string $viewDisplay
   *   The name of the view display.
   *
   * @return \Drupal\Component\Render\MarkupInterface
   *   Returns a Markup with the view rendered.
   */
  private function renderView(string $viewMachineName, string $viewDisplay): MarkupInterface {
    $view = Views::getView($viewMachineName);
    $view->setDisplay($viewDisplay);
    $view->setAjaxEnabled(FALSE);

    $rendered = $view->render();

    return $this->renderer->render($rendered);
  }

  /**
   * Return the ViewExecutable from the specified view.
   *
   * @param string $viewMachineName
   *   The machine name of the view.
   * @param string $viewDisplay
   *   The name of the view display.
   *
   * @return \Drupal\views\ViewExecutable
   *   Returns a array with the view rendered.
   */
  private function executeView(string $viewMachineName, string $viewDisplay): ViewExecutable {
    $view = Views::getView($viewMachineName);
    $view->setDisplay($viewDisplay);
    $view->setAjaxEnabled(FALSE);
    $view->execute();

    return $view;
  }

  /**
   * Get the field inside the view.
   *
   * @param \Drupal\views\ViewExecutable $view
   *   The executable view.
   *
   * @return int
   *   Returns the value of the field.
   */
  private function getFieldInsideView(ViewExecutable $view): int {
    foreach ($view->result as $row) {
      foreach ($view->field as $field) {
        if ($field->getValue($row) !== NULL) {
          return (int) $field->getValue($row);
        }
      }
    }

    return 0;
  }

  /**
   * Get the value of the config field.
   *
   * @param string $configField
   *   The machine name of the config field.
   *
   * @return int
   *   The value of the config.
   */
  private function getConfigField(string $configField): int {
    $countersConfig = $this->config('umio_front_area.umio_counters');
    $value = $countersConfig->get($configField);
    if (!$value) {
      return 0;
    }

    return (int) $value;
  }

}
