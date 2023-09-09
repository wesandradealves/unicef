<?php

namespace Drupal\umio_batch_import\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Service to get the TID of taxonomy term name.
 */
class BatchImportTaxonomyService {

  /**
   * Array to save taxonomies.
   *
   * @var array
   */
  private $taxonomies = [];

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The construct method.
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
  public static function create(ContainerInterface $container): BatchImportTaxonomyService {
    return new static(
      $container->get('entity_type.manager'),
    );
  }

  /**
   * Return the tid of the taxonomy term name.
   *
   * @param string $vocabularyName
   *   The vocabulary name to search the taxonomy term name.
   * @param string $termName
   *   The term name to search it.
   *
   * @return string|null
   *   The tid of the term.
   */
  public function getTidByTaxonomyTermName(string $vocabularyName, string $termName): ?string {
    $taxonomyStorage = $this->entityTypeManager->getStorage('taxonomy_term');
    if (!isset($this->taxonomies[$vocabularyName])) {
      $terms = $taxonomyStorage->loadByProperties([
        'vid' => $vocabularyName,
      ]);
      $this->taxonomies[$vocabularyName] = $terms;
    }

    foreach ($this->taxonomies[$vocabularyName] as $term) {
      $name = $term->get('name')->getValue()[0]['value'];
      if (strtolower($name) === strtolower($termName)) {
        return $term->get('tid')->getValue()[0]['value'];
      }
    }

    return NULL;
  }

  /**
   * Return the tid of the custom config terms.
   *
   * @param string $customFieldName
   *   The name of the field to load the configuration..
   * @param string $termName
   *   The term name to search it.
   * @param string $bundle
   *   The bundle type to search the $customFieldName.
   *
   * @return string|null
   *   The tid of the term.
   */
  public function getTidFromCustomTerm(string $customFieldName, string $termName, string $bundle): ?string {
    $bundle_fields = \Drupal::getContainer()->get('entity_field.manager')->getFieldDefinitions('node', $bundle);
    $field_configuration = $bundle_fields[$customFieldName]->getSettings();
    if (!empty($field_configuration['allowed_values'])) {
      $allowed_values = $field_configuration['allowed_values'];
      foreach ($allowed_values as $key => $value) {
        if ($value == $termName) {
          return $key;
        }
      }
    }

    return NULL;
  }

}
