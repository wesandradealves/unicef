<?php

namespace Drupal\umio_batch_import\Form\Vacancy;

use Drupal\Core\Entity\EntityConstraintViolationListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Drupal\umio_batch_import\Form\AbstractBatchImportForm;
use PhpOffice\PhpSpreadsheet\Shared\Date;

/**
 * Form to import multiples vacancy nodes.
 */
class VacancyBatchImportForm extends AbstractBatchImportForm implements VacancyConstInterface {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'vacancy_batch_import';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form = parent::buildForm($form, $form_state);
    $form['#attributes']['class'][] = 'vacancy-import-form';
    $form['#attached']['library'] = [
      'umio_vacancy/umio_vacancy.form',
    ];

    $vacancyConfig = $this->config('umio_batch_import.vacancy.settings');
    if (!isset($form[AbstractBatchImportForm::STATUS_FIELD])) {
      $markup = $vacancyConfig->get('vacancy_template_markup');
    }
    else {
      $markup = $vacancyConfig->get('vacancy_after_submit_markup');
    }

    $form['details'] = [
      '#markup' => $markup,
      '#weight' => 1,
      '#prefix' => '<div class="batch-import-text text-break">',
      '#suffix' => '</div>',
    ];

    $form['file']['#weight'] = 2;

    $form['actions']['submit']['#attributes']['class'] = ['d-none'];
    $form['actions']['decent_work'] = [
      '#type' => 'button',
      '#value' => $this->t('Send sheet'),
      '#name' => 'decent_work_modal',
      '#attributes' => [
        'class' => [
          'btn',
          'btn-primary',
        ],
      ],
    ];

    return $form;
  }

  /**
   * {@inheritDoc}
   */
  protected function getTemplateHeader(): array {
    return VacancyConstInterface::HEADER_SPREEADSHEET;
  }

  /**
   * {@inheritDoc}
   */
  protected function customViolations(EntityConstraintViolationListInterface $violations): EntityConstraintViolationListInterface {
    $vacancy = $violations->getEntity();
    $salaryOptions = $vacancy->get('field_vacancy_salary_options')->getValue()[0]['value'];
    foreach ($violations as $key => $violation) {
      $field = $violation->getPropertyPath();
      if ($salaryOptions === 'unique') {
        if ($field === 'field_vacancy_salary_min' || $field === 'field_vacancy_salary_max') {
          unset($violations[$key]);
        }
      }
      elseif ($salaryOptions === 'min_max') {
        if ($field === 'field_vacancy_salary') {
          unset($violations[$key]);
        }
      }
    }

    return $violations;
  }

  /**
   * {@inheritDoc}
   */
  protected function getFieldMachineNameByColumn(string $column): string {
    if (isset(VacancyConstInterface::FIELDS_SPREEADSHEET[$column])) {
      return VacancyConstInterface::FIELDS_SPREEADSHEET[$column];
    }

    return '';
  }

  /**
   * {@inheritDoc}
   */
  protected function transformData(string $field, string $headerColumn, ?string $value): ?string {
    if ($value === '') {
      return NULL;
    }

    switch ($field) {
      case 'title':
      case 'field_vacancy_quantity':
      case 'field_vacancy_activities':
      case 'field_vacancy_life_competence':
        return $value;

      case 'field_vacancy_type':
        return $this->transformByOptionType(VacancyConstInterface::FIELD_VACANCY_TYPE, $value);

      case 'field_vacancy_job_model':
        return $this->transformByOptionType(VacancyConstInterface::FIELD_VACANCY_JOB_MODEL, $value);

      case 'field_vacancy_closing_date':
        $closingDate = \DateTime::createFromFormat('dd/mm/YYYY', $value);
        if ($closingDate) {
          return $closingDate->format('Y-m-d');
        }

        // Libreoffice returns int in date field.
        if (intval($value)) {
          $date = Date::excelToDateTimeObject(intval($value));
          // Libreoffice create date with any integer
          // Check if the date is greater than now.
          if ($date >= new \DateTime('now')) {
            return $date->format('Y-m-d');
          }
        }
        return $value;

      case 'field_vacancy_salary_options':
        return $this->transformByOptionType(VacancyConstInterface::FIELD_VACANCY_SALARY_OPTIONS, $value);

      case 'field_vacancy_salary':
      case 'field_vacancy_salary_min':
      case 'field_vacancy_salary_max':
        if ($value) {
          return str_replace(',', '.', $value);
        }
        return NULL;

      case 'field_vacancy_subscription_url':
        return $value;

      case 'field_vacancy_city':
        return $value;

      case 'field_vacancy_state':
        return $value;

      case 'field_vacancy_skills_match':
        if ($value === '=true()' || $value === '1') {
          $tid = $this->taxonomyService->getTidFromCustomTerm($field, $headerColumn, 'vacancy');
          return $tid ? $tid : $value;
        }

        if ($value === '=FALSE()' || $value === '0') {
          return NULL;
        }

        return $value;

      case 'field_vacancy_benefits':
        if ($value === '=true()' || $value === '1') {
          $tid = $this->taxonomyService->getTidByTaxonomyTermName('vacancy_benefits', $headerColumn);
          return $tid ? $tid : $value;
        }

        if ($value === '=FALSE()' || $value === '0') {
          return NULL;
        }

        return $value;

      case 'field_vacancy_priority_profiles':
        if ($value === '=true()' || $value === '1') {
          $tid = $this->taxonomyService->getTidByTaxonomyTermName('company_public', $headerColumn);
          return $tid ? $tid : $value;
        }

        if ($value === '=FALSE()' || $value === '0') {
          return NULL;
        }

        return $value;

    }

    return $value;
  }

  /**
   * {@inheritDoc}
   */
  protected function createEntity(array $data): Node {
    $node = Node::create([
      'type' => 'vacancy',
    ]);

    foreach ($data as $field => $value) {
      if ($value) {
        $node->set($field, $value);
      }
    }

    // Removing default value if not set this field in spreadsheet.
    if (!isset($data['field_vacancy_quantity'])) {
      $node->set('field_vacancy_quantity', 0);
    }

    return $node;
  }

  /**
   * Check if the value is in the types array and return the key.
   *
   * @param array $types
   *   Array with the key and value of allowed type.
   * @param string|null $value
   *   Value of the option.
   *
   * @return string|null
   *   Return the key of the option selected.
   */
  private function transformByOptionType(array $types, ?string $value): ?string {
    $key = array_search($value, $types);
    if ($key !== FALSE) {
      return $key;
    }

    return $value;
  }

}
