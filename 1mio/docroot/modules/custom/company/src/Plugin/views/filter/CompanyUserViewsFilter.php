<?php

namespace Drupal\company\Plugin\views\filter;

use Drupal\Core\Form\FormStateInterface;
use Drupal\umio_user\Service\UserService;
use Drupal\user\Entity\User;
use Drupal\views\Plugin\views\filter\FilterPluginBase;
use Drupal\views\Views;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Filtering for company field in user content type.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("company_user_filter")
 */
class CompanyUserViewsFilter extends FilterPluginBase {

  /**
   * Define the UserService.
   *
   * @var \Drupal\umio_user\Service\UserService
   */
  protected $userService;

  /**
   * Where the $query object will reside.
   *
   * @var \Drupal\views\Plugin\views\query\Sql
   */
  public $query;

  /**
   * {@inheritdoc}
   */
  final public function __construct(array $configuration, $plugin_id, $plugin_definition, UserService $userService) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->userService = $userService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('umio_user.user_service'),
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['include_current_user'] = [
      'default' => '0',
    ];
    return $options;
  }

  /**
   * Provide the basic form which calls through to subforms.
   *
   * If overridden, it is best to call through to the parent,
   * or to at least make sure all of the functions in this form
   * are called.
   *
   * @param mixed $form
   *   The current form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state): void {
    $form['include_current_user'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Include current user'),
      '#default_value' => $this->options['include_current_user'],
    ];
    parent::buildOptionsForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function query(): void {
    $this->ensureMyTable();
    $view = $this->query->view;
    if (!isset($view->args[0])) {
      return;
    }
    $uid = $view->args[0];
    $user = User::load($uid);
    if ($user) {
      $companies = $this->userService->getAllBranchCompaniesForTheUser($user);
      $configuration = [
        'table' => $this->getTableName(),
        'field' => 'entity_id',
        'left_table' => 'users_field_data',
        'left_field' => 'uid',
        'operator' => '=',
      ];
      $join = Views::pluginManager('join')->createInstance('standard', $configuration);
      $this->query->addRelationship($this->getTableName(), $join, 'users_field_data');
      if ($companies) {
        $this->query->addWhere($this->options['group'], $this->getTableName() . '.' . $this->getFieldName(), $companies, 'IN');
        if (!$this->options['include_current_user']) {
          // Not return the user data.
          $this->query->addWhere($this->options['group'], 'users_field_data.uid', $user->id(), '!=');
        }
      }
      else {
        $arrayEmpty = [0 => 0];
        $this->query->addWhere($this->options['group'], $this->getTableName() . '.' . $this->getFieldName(), $arrayEmpty, 'IN');
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  private function getTableName(): string {
    return 'user__field_user_company';
  }

  /**
   * {@inheritdoc}
   */
  private function getFieldName(): string {
    return 'field_user_company_target_id';
  }

}
