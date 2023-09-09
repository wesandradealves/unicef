<?php

namespace Drupal\umio_batch_import\Form\CityStamp;

use Drupal\company\Service\CityStampService;
use Drupal\Core\Entity\EntityConstraintViolationListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\taxonomy\Entity\Term;
use Drupal\umio_batch_import\Form\AbstractBatchImportForm;

/**
 * Form to import multiples city nodes.
 */
class CityStampBatchImportForm extends AbstractBatchImportForm implements CityStampConstInterface {

  /**
   * Array with all states tid.
   *
   * @var array
   */
  private $statesTerms = [];

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'city_batch_import';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form = parent::buildForm($form, $form_state);
    $form['#attributes']['class'][] = 'city-import-form';

    $cityConfig = $this->config('umio_batch_import.city_stamp.settings');
    if (!isset($form[AbstractBatchImportForm::STATUS_FIELD])) {
      $markup = $cityConfig->get('city_stamp_template_markup');
    }
    else {
      $markup = $cityConfig->get('city_stamp_after_submit_markup');
    }

    $form['details'] = [
      '#markup' => $markup,
      '#weight' => 1,
      '#prefix' => '<div class="batch-import-text text-break">',
      '#suffix' => '</div>',
    ];

    $form['file']['#weight'] = 2;

    return $form;
  }

  /**
   * {@inheritDoc}
   */
  protected function getTemplateHeader(): array {
    return CityStampConstInterface::HEADER_SPREEADSHEET;
  }

  /**
   * {@inheritDoc}
   */
  protected function customViolations(EntityConstraintViolationListInterface $violations): EntityConstraintViolationListInterface {
    return $violations;
  }

  /**
   * {@inheritDoc}
   */
  protected function getFieldMachineNameByColumn(string $column): string {
    if (isset(CityStampConstInterface::FIELDS_SPREEADSHEET[$column])) {
      return CityStampConstInterface::FIELDS_SPREEADSHEET[$column];
    }

    return '';
  }

  /**
   * {@inheritDoc}
   */
  protected function createEntity(array $data): Term {
    if (!$this->statesTerms) {
      $this->statesTerms = $this->getAllStatesTid();
    }

    $term = Term::create([
      'vid' => CityStampService::STAMP_VOCABULARY,
      'name' => $data['name'],
      'parent' => $this->statesTerms[$data['state']],
    ]);
    $term->set('field_stamp_unicef_ibge', $data['field_stamp_unicef_ibge']);

    return $term;
  }

  /**
   * {@inheritDoc}
   */
  protected function transformData(string $field, string $headerColumn, ?string $value): ?string {
    return $value;
  }

  /**
   * Function to get all stated tid from the vocabulary.
   *
   * @return array
   *   Array with the states and the tid of that state.
   */
  private function getAllStatesTid(): array {
    /** @var \Drupal\taxonomy\TermStorage $termStorage */
    $termStorage = $this->entityTypeManager->getStorage('taxonomy_term');
    $terms = $termStorage->loadByProperties([
      'vid' => CityStampService::STAMP_VOCABULARY,
    ]);
    if (!$terms) {
      return $this->createAndGetTerms();
    }

    $statesTerms = [];
    foreach ($terms as $term) {
      /** @var \Drupal\taxonomy\Entity\Term $term */

      // If the term don't have a parent it's a region.
      if (!$term->get('parent')->getString()) {
        continue;
      }

      // If the term has a parent but have a IBGE it's a city.
      if ($term->get('field_stamp_unicef_ibge')->getString()) {
        continue;
      }

      $name = $term->get('name')->getString();
      $state = array_search($name, self::LIST_STATES);
      $statesTerms[$state] = $term->id();
    }

    return $statesTerms;
  }

  /**
   * Create and return the states and your tid.
   *
   * @return array
   *   Array with the states and the tid of that state.
   */
  private function createAndGetTerms(): array {
    $statesTerms = [];
    // Saving regions.
    foreach (self::REGION_STATES as $region) {
      $termRegion = Term::create([
        'vid' => CityStampService::STAMP_VOCABULARY,
        'name' => $region['name'],
        'parent' => $region['parent'],
      ]);
      $termRegion->save();
      // Saving states.
      foreach ($region['states'] as $state) {
        $termState = Term::create([
          'vid' => CityStampService::STAMP_VOCABULARY,
          'name' => self::LIST_STATES[$state],
          'parent' => $termRegion->id(),
        ]);
        $termState->set('field_stamp_unicef_acronym', $state);
        $termState->save();
        $statesTerms[$state] = $termState->id();
      }
    }

    return $statesTerms;
  }

}
