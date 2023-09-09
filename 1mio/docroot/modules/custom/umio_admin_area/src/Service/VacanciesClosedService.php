<?php

namespace Drupal\umio_admin_area\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\user\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a VacanciesClosedService.
 */
class VacanciesClosedService {

  /**
   * The field of the paragraph.
   *
   * @var array
   */
  private const PARAGRAPH_FIELDS = [
    'field_disabilities_youngs',
    'field_younger_mothers',
    'field_socio_educational_system',
    'field_ethnic_racial_equity',
    'field_lgbtia',
    'field_location',
    'field_girls_science',
    'field_originating_populations',
    'field_low_income',
    'field_child_labor_victims',
  ];

  /**
   * The id of the priority profile taxonomy.
   *
   * @var string
   */
  private const PRIORITY_PROFILE_TAXONOMY_ID = 'company_public';

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * The constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   */
  final public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new static(
      $container->get('entity_type.manager'),
    );
  }

  /**
   * Get the terms from priority profile taxonomy.
   *
   * @return array
   *   The terms.
   */
  public function getPriorityProfileTerms(): array {
    /** @var \Drupal\taxonomy\TermStorage $storage */
    $storage = $this->entityTypeManager->getStorage('taxonomy_term');
    /** @var \Drupal\taxonomy\Entity\Term[] $terms */
    $terms = $storage->loadTree(self::PRIORITY_PROFILE_TAXONOMY_ID, 0, NULL, TRUE);

    return $terms;
  }

  /**
   * Get the user vacancies closed by term id.
   *
   * @param \Drupal\user\Entity\User $user
   *   The user to get vacancies closed.
   *
   * @return array
   *   Array with the vacancies closed by term id.
   */
  public function getVacanciesClosedByTerm(User $user): array {
    $paragraphs = $this->getPriorityProfileParagraphs($user);

    $paragraph = $paragraphs[0];
    $values = [];
    if ($paragraph) {
      foreach (self::PARAGRAPH_FIELDS as $field) {
        $values[$field] = $paragraph->get($field)->getString();
      }
    }

    return $values;
  }

  /**
   * Get the priority profile paragraphs from the user.
   *
   * @param \Drupal\user\Entity\User $user
   *   The user to priority profile paragraphs.
   *
   * @return array
   *   Array with the priority profile paragraphs.
   */
  public function getPriorityProfileParagraphs(User $user): array {
    /** @var \Drupal\entity_reference_revisions\EntityReferenceRevisionsFieldItemList $itemList */
    $itemList = $user->get('field_vacancies_priority_profile');
    $paragraphs = $itemList->referencedEntities();

    return $paragraphs;
  }

}
