<?php

namespace Drupal;

use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\TableNode;
use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Driver\Exception\Exception;
use Drupal\DrupalExtension\Context\RawDrupalContext;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\FieldConfigInterface;
use TravisCarden\BehatTableComparison\TableEqualityAssertion;
use Drupal\pathauto\Entity\PathautoPattern;

/**
 * ContentModelContext class defines custom step definitions for Behat.
 */
class ContentModelContext extends RawDrupalContext implements SnippetAcceptingContext {

  /**
   * @var \Drupal\content_translation\ContentTranslationManagerInterface
   */
  private $contentTranslationManager;

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  private $configFactory;

  public function __construct() {
    if (\Drupal::hasService('content_translation.manager')) {
      $this->contentTranslationManager = \Drupal::service('content_translation.manager');
    }
    $this->entityTypeManager = \Drupal::entityTypeManager();
    $this->configFactory = \Drupal::configFactory();
  }

  /**
   * @Then exactly the following entity type bundles should exist
   */
  public function assertBundles(TableNode $expected) {
    $bundle_info = [];
    foreach ($this->getEntityTypesWithBundles() as $entity_type) {
      $bundles = $this->entityTypeManager
        ->getStorage($entity_type->getBundleEntityType())
        ->loadMultiple();

      foreach ($bundles as $bundle) {
        $is_moderated = $bundle->getThirdPartySetting('workbench_moderation', 'enabled');
        $is_translatable = $this->contentTranslationManager
          ->isEnabled($entity_type->id(), $bundle->id());
        $description_getter = 'getDescription';
        $description = '';
        if (method_exists($bundle, $description_getter)) {
          $description = call_user_func([
            $bundle,
            $description_getter,
          ]);
        }

        $bundle_info[] = [
          $entity_type->getBundleLabel(),
          $bundle->label(),
          $bundle->id(),
          $is_translatable ? 'translatable' : '',
          $is_moderated ? 'moderated' : '',
          is_scalar($description) ? $description : '',
        ];
      }
    }
    $actual = new TableNode($bundle_info);

    (new TableEqualityAssertion($expected, $actual))
      ->expectHeader([
        'type',
        'label',
        'machine name',
        'translatable',
        'moderated',
        'description',
      ])
      ->ignoreRowOrder()
      ->setMissingRowsLabel('Missing bundles')
      ->setUnexpectedRowsLabel('Unexpected bundles')
      ->assert();
  }

  /**
   * @Then exactly the following fields should exist
   */
  public function assertFields(TableNode $expected) {
    $fields = [];
    foreach ($this->getEntityTypesWithBundles() as $entity_type) {
      $bundles = $this->entityTypeManager
        ->getStorage($entity_type->getBundleEntityType())
        ->loadMultiple();
      foreach ($bundles as $bundle) {
        $display_id = "{$entity_type->id()}.{$bundle->id()}.default";
        $form_display = EntityFormDisplay::load($display_id);
        if (is_null($form_display)) {
          continue;
          // @todo: Determine how to handle this correctly.
          // throw new \Exception(sprintf('No such form display %s.', $display_id));
        }
        $form_components = $form_display->getComponents();

        /** @var string[] $ids */
        $ids = \Drupal::entityQuery('field_config')
          ->condition('bundle', $bundle->id())
          ->execute();
        /** @var FieldConfigInterface $field_config */
        foreach (FieldConfig::loadMultiple($ids) as $id => $field_config) {
          $machine_name = $this->getFieldMachineNameFromConfigId($id);
          // @todo: Once panelizer is fully removed.
          if ($machine_name == 'panelizer') {
            continue;
          }
          $field_storage = $field_config->getFieldStorageDefinition();
          $form_component = isset($form_components[$machine_name]) ? $form_components[$machine_name] : ['type' => 'hidden'];
          $fields[] = [
            $entity_type->getBundleLabel(),
            $bundle->label(),
            $field_config->getLabel(),
            $machine_name,
            $field_config->getType(),
            $field_config->isRequired() ? 'required' : '',
            $field_config->isTranslatable() ? 'translatable' : '',
            $field_storage->getCardinality() === -1 ? 'unlimited' : $field_storage->getCardinality(),
            $form_component['type'],
            // @todo: Remove this, no description should be multi-lines.
            str_replace(["\n", "\r\n", "\r"], [' ', ' ', ' '], $field_config->getDescription()),
          ];
        }
      }
    }
    $actual = new TableNode($fields);

    (new TableEqualityAssertion($expected, $actual))
      ->expectHeader([
        'entity type',
        'bundle',
        'label',
        'machine name',
        'type',
        'required',
        'translatable',
        'cardinality',
        'widget',
        'description',
      ])
      ->ignoreRowOrder()
      ->setMissingRowsLabel('Missing fields')
      ->setUnexpectedRowsLabel('Unexpected fields')
      ->assert();
  }

  /**
   * @Then exactly the following languages should exist
   */
  public function assertLanguages(TableNode $expected) {
    $language_info = [];

    $language_negotiation = $this->configFactory->get('language.negotiation');
    $prefixes = $language_negotiation->get('url')['prefixes'];

    foreach ($this->getLanguages() as $language) {
      // @todo: Once we've determine how we validate and test *all* langs.
      if ($language->getId() != 'en') {
        continue;
      }

      $language_info[] = [
        $language->getId(),
        $language->getName(),
        isset($prefixes[$language->getId()]) ? $prefixes[$language->getId()] : '',
      ];
    }
    $actual = new TableNode($language_info);

    (new TableEqualityAssertion($expected, $actual))
      ->expectHeader([
        'id',
        'name',
        'prefix',
      ])
      ->ignoreRowOrder()
      ->setMissingRowsLabel('Missing languages')
      ->setUnexpectedRowsLabel('Unexpected languages')
      ->assert();
  }

  /**
   * @Then exactly the following path alias patterns should exist
   */
  public function assertPathAutoPatterns(TableNode $expected) {
    $module_handler = \Drupal::moduleHandler();
    if (!$module_handler->moduleExists('pathauto')) {
      throw new \Exception(sprintf('The Drupal contributed module Pathauto does not exist.'));
    }

    $pathauto_pattern_info = [];

    /** @var PathautoPattern $pathauto_pattern */
    foreach (PathautoPattern::loadMultiple() as $pathauto_pattern) {
      $bundles = '';
      $langcodes = '';
      $selection_conditions_config = $pathauto_pattern->getSelectionConditions()->getConfiguration();
      foreach ($selection_conditions_config as $key => $condition) {
        if (isset($condition['bundles'])) {
          $bundles = implode(', ', $condition['bundles']);
        }
        if (isset($condition['langcodes'])) {
          $langcodes = implode(', ', $condition['langcodes']);
        }
      }
      $pathauto_pattern_info[] = [
        $pathauto_pattern->label(),
        $bundles,
        $langcodes,
        $pathauto_pattern->getPattern(),
      ];
    }
    $actual = new TableNode($pathauto_pattern_info);

    (new TableEqualityAssertion($expected, $actual))
      ->expectHeader([
        'label',
        'bundles',
        'languages',
        'pattern',
      ])
      ->ignoreRowOrder()
      ->setMissingRowsLabel('Missing patterns')
      ->setUnexpectedRowsLabel('Unexpected patterns')
      ->assert();
  }

  /**
   * @Then the image fields on the site should be configured exactly as follows
   */
  public function assertImageFieldConfiguration(TableNode $expected) {
    $fields = [];
    /** @var string[] $ids */
    $ids = \Drupal::entityQuery('field_config')
      ->condition('field_type', 'image')
      ->execute();
    /** @var FieldConfigInterface $field_config */
    foreach (FieldConfig::loadMultiple($ids) as $id => $field_config) {
      $fields[] = [
        $field_config->getTargetBundle(),
        $field_config->getLabel(),
        $this->getFieldMachineNameFromConfigId($id),
        $field_config->getSetting('file_directory'),
      ];
    }
    $actual = new TableNode($fields);

    (new TableEqualityAssertion($expected, $actual))
      ->expectHeader([
        // @todo Change to "bundle" and use bundle machine name.
        'bundle id',
        // @todo Add "type" for entity type (label).
        'label',
        'machine name',
        'file directory',
      ])
      ->ignoreRowOrder()
      ->setMissingRowsLabel('Missing image fields')
      ->setUnexpectedRowsLabel('Unexpected image fields')
      ->assert();

  }

  /**
   * Gets the defined entity types that have bundles.
   *
   * @return \Drupal\Core\Entity\EntityTypeInterface[]
   *   An array of entity types.
   */
  protected function getEntityTypesWithBundles() {
    $entity_types = $this->entityTypeManager->getDefinitions();
    foreach ($entity_types as $id => $entity_type) {
      // Remove entity types that don't have bundles.
      $bundle_label = $entity_type->getBundleLabel();
      $bundle_entity_type = $entity_type->getBundleEntityType();
      if (empty($bundle_label) || empty($bundle_entity_type)) {
        unset($entity_types[$id]);
      }
    }
    return $entity_types;
  }

  /**
   * Gets the field machine name from a configuration object ID.
   *
   * @param string $id
   *   The field configuration object ID.
   *
   * @return string|false
   *   The machine name if found or FALSE if not.
   */
  protected function getFieldMachineNameFromConfigId($id) {
    return substr($id, strrpos($id, '.') + 1);
  }

  /**
   * Gets the defined language entities.
   *
   * @return \Drupal\Core\Language\LanguageInterface[]
   *   An array of language entities.
   */
  protected function getLanguages() {
    $languages = \Drupal::languageManager()->getLanguages();
    return $languages;
  }

}
