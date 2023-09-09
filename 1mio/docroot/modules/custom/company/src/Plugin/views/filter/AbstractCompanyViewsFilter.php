<?php

namespace Drupal\company\Plugin\views\filter;

use Drupal\umio_user\Service\UserService;
use Drupal\views\Plugin\views\filter\FilterPluginBase;
use Drupal\views\Views;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Abstract filtering for company fields inside content types.
 */
abstract class AbstractCompanyViewsFilter extends FilterPluginBase {

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
   * Get the table name of the field relates to the company.
   *
   * @return string
   *   Table name of the field that relates to the company content type.
   */
  abstract protected function getTableName(): string;

  /**
   * Get the field name relates to the company.
   *
   * @return string
   *   Field name that relates to the company content type.
   */
  abstract protected function getFieldName(): string;

  /**
   * Adds conditions to the query based on the selected filter option.
   */
  public function query(): void {
    $this->ensureMyTable();
    $companies = $this->userService->getAllBranchCompaniesForTheCurrentUser();
    if ($companies) {
      $configuration = [
        'table' => $this->getTableName(),
        'field' => 'entity_id',
        'left_table' => 'node_field_data',
        'left_field' => 'nid',
        'operator' => '=',
      ];
      $join = Views::pluginManager('join')->createInstance('standard', $configuration);
      $this->query->addRelationship($this->getTableName(), $join, 'node_field_data');
      $this->query->addWhere($this->options['group'], $this->getTableName() . '.' . $this->getFieldName(), $companies, 'IN');
    }
  }

}
