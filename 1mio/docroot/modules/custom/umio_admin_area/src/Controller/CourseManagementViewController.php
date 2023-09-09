<?php

namespace Drupal\umio_admin_area\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\Renderer;
use Drupal\umio_user\Service\UserService;
use Drupal\views\Views;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class CourseManagementViewController to render the view course_management.
 */
class CourseManagementViewController extends ControllerBase {

  /**
   * Define the UserService.
   *
   * @var \Drupal\umio_user\Service\UserService
   */
  private $userService;

  /**
   * Define the renderer.
   *
   * @var \Drupal\Core\Render\Renderer
   */
  private $renderer;

  /**
   * Define the constructor.
   */
  final public function __construct(UserService $userService, Renderer $renderer) {
    $this->userService = $userService;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) : self {
    return new static(
      $container->get('umio_user.user_service'),
      $container->get('renderer'),
    );
  }

  /**
   * Renders the people management page.
   *
   * @return array
   *   Returns a array with the view rendered.
   */
  public function render(): array {

    $view = Views::getView('course_management');
    $view->setDisplay('default');
    $view->setAjaxEnabled(FALSE);

    $companies = $this->userService->getAllBranchCompaniesForTheCurrentUser();

    $filters = $view->getDisplay()->getOption('filters');
    $filter_pattern = $filters["field_course_institution_target_id"];

    unset($filters["field_course_institution_target_id"]);

    if ($companies) {
      for ($i = 0; $i < count($companies); $i++) {
        $j = $i + 1;
        $filters["field_course_institution_target_id_{$j}"] = $filter_pattern;
        $filters["field_course_institution_target_id_{$j}"]['value']['value'] = $companies[$i];
      }
    }

    $view->getDisplay()->overrideOption('filters', $filters);
    $view->execute();

    $rendered = $view->render();

    unset($rendered['#view']->exposed_widgets['#action']);

    $output = $this->renderer->render($rendered);

    return [
      '#title' => $this->t('Courses'),
      '#markup' => $output,
    ];

  }

}
