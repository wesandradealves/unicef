<?php

namespace Drupal\umio_younger\Plugin\views\filter;

use Drupal\Core\Session\AccountProxy;
use Drupal\umio_younger\Service\YoungService;
use Drupal\views\Plugin\views\filter\FilterPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Check if vacancy skill field is equal the skill of the younger user.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("vacancy_recommended_match_filter")
 */
class VacancyYoungerRecommendedViewsFilter extends FilterPluginBase {

  /**
   * Define the current user.
   *
   * @var \Drupal\Core\Session\AccountProxy
   */
  protected $account;

  /**
   * Where the $query object will reside.
   *
   * @var \Drupal\search_api\Plugin\views\query\SearchApiQuery
   */
  public $query;

  /**
   * The young service.
   *
   * @var \Drupal\umio_younger\Service\YoungService
   */
  private $youngService;

  /**
   * Constructs a VacancyYoungerSkillsViewsFilter object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Session\AccountProxy $account
   *   The current user.
   * @param \Drupal\umio_younger\Service\YoungService $youngService
   *   The young service.
   */
  final public function __construct(
    array $configuration,
    string $plugin_id,
    $plugin_definition,
    AccountProxy $account,
    YoungService $youngService
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->account = $account;
    $this->youngService = $youngService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_user'),
      $container->get('umio_younger.young_service')
    );
  }

  /**
   * Adds conditions to the query based on the selected filter option.
   */
  public function query(): void {
    $this->ensureMyTable();

    $skills = $this->youngService->getSkillsFromTheUser($this->account);
    $skillFieldName = $this->getSkillsFieldName();

    if (!empty($skills)) {
      $this->query->addWhere($this->options['group'], $skillFieldName, $skills, 'IN');
    }

    $profiles = $this->youngService->getPriorityProfileFromTheUser($this->account);
    $priorityFieldName = $this->getPriorityProfileFieldName();

    if (!empty($profiles)) {
      $this->query->addWhere($this->options['group'], $priorityFieldName, $profiles, 'IN');
    }

    // If neither skills or profiles make the query returns 0 results.
    if (empty($skills) && empty($profiles)) {
      $arrayEmpty = [0 => 0];
      $this->query->addWhere($this->options['group'], $skillFieldName, $arrayEmpty, 'IN');
      $this->query->addWhere($this->options['group'], $priorityFieldName, $arrayEmpty, 'IN');
    }
  }

  /**
   * Get the field name relates to the skills field in vacancy.
   *
   * @return string
   *   Field name that relates to the skills field in vacancy.
   */
  private function getSkillsFieldName(): string {
    return 'field_vacancy_skills_match';
  }

  /**
   * Get the field name relates to the priority profiles in vacancy.
   *
   * @return string
   *   Field name that relates to the priority profiles in vacancy.
   */
  private function getPriorityProfileFieldName(): string {
    return 'field_vacancy_priority_profiles_tid';
  }

}
