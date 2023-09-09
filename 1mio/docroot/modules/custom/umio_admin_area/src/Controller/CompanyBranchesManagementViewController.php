<?php

namespace Drupal\umio_admin_area\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Drupal\umio_user\Service\UserService;
use Drupal\views\Views;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class CompanyBranchesManagementViewController to render the view.
 */
class CompanyBranchesManagementViewController extends ControllerBase {

  /**
   * Define the FormValidator.
   *
   * @var \Drupal\umio_user\Service\UserService
   */
  private $userService;

  /**
   * Define the constructor.
   */
  final public function __construct(UserService $userService) {
    $this->userService = $userService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) : self {
    return new static(
      $container->get('umio_user.user_service'),
    );
  }

  /**
   * Renders the people management page.
   *
   * @return array
   *   Returns a array with the view rendered.
   */
  public function render(): array {

    $view = Views::getView('company_branches_management');
    $view->setDisplay('default');
    $view->setAjaxEnabled(FALSE);

    $userMainCompany = $this->userService->getCurrentUserMainCompany();

    if ($userMainCompany) {
      $userMainCompany = Node::Load($userMainCompany);
      $companies = $this->userService->getOnlyBranchsCompaniesForTheCurrentUser();
      $filters = $view->getDisplay()->getOption('filters');
      $filter_pattern = $filters['nid'];
      if ($companies != []) {
        unset($filters['nid']);
      }
      if ($companies) {
        for ($i = 0; $i < count($companies); $i++) {
          $j = $i + 1;
          $filters["nid_{$j}"] = $filter_pattern;
          $filters["nid_{$j}"]['value']['value'] = $companies[$i];
        }
      }
      $view->getDisplay()->overrideOption('filters', $filters);

      $fields = $view->getDisplay()->getOption('fields');
      $userMainCompanyType = $userMainCompany->get('field_company_type')->getString();
      if ($userMainCompanyType != "company") {
        unset($fields['field_company_cnpj']);
      }
      $view->getDisplay()->overrideOption('fields', $fields);
      $view->execute();
    }

    $rendered = $view->render();

    unset($rendered['#view']->exposed_widgets['#action']);

    $output = \Drupal::service('renderer')->render($rendered);

    return [
      '#title' => $this->t('Branches'),
      '#markup' => $output,
    ];

  }

}
