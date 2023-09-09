<?php

namespace Drupal\umio_vacancy\Service;

use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Class to alter vacancy form.
 */
class VacancyFormService {

  use StringTranslationTrait;

  /**
   * Function to alter the vacancy form.
   *
   * @param array $form
   *   The vacancy form.
   *
   * @return array
   *   The vacancy form.
   */
  public function formAlter(array $form): array {
    // Validate custom.
    $form['#validate'][] = 'umio_vacancy_validate_custom';

    // Attach a library.
    $form['#attached']['library'][] = 'umio_vacancy/umio_vacancy.form';
    $form['#attached']['library'][] = 'umio_helpers/umio_helpers.mask';
    $form['#attached']['library'][] = 'umio_helpers/umio_helpers.select2_customization';

    // Remove none from selects fields.
    $form = $this->removeNoneOptionSelect($form, 'field_vacancy_type');
    $form = $this->removeNoneOptionSelect($form, 'field_vacancy_job_model');

    // Adding required to vacancy_company.
    $form['field_vacancy_company']['widget']['#required'] = TRUE;

    $subformAddress = $form["field_vacancy_address"]["widget"][0]["subform"]["field_paragraph_address"] ?? [];
    $addressWidgets = $subformAddress["widget"][0]["address"]["#field_overrides"] ?? [];

    $allowedFields = ['locality', 'administrativeArea'];
    foreach ($addressWidgets as $fieldName => $widget) {
      if (!in_array($fieldName, $allowedFields)) {
        $addressWidgets[$fieldName] = "hidden";
      }
    }

    $form["field_vacancy_address"]["widget"][0]["subform"]["field_paragraph_address"]["widget"][0]["address"]["#field_overrides"] = $addressWidgets;

    // Field Salary Options.
    $form['field_vacancy_salary_options']['widget']['#required'] = FALSE;

    // Field Vacancy Address.
    $form['field_vacancy_state']['widget']['#required'] = TRUE;
    $form['field_vacancy_city']['widget']['#required'] = TRUE;

    // Field Salary.
    $form['field_vacancy_salary']['#attributes']['class'][] = 'form-item--hide-title';

    // Fields Salary Min and Max.
    $classSalariesRange = 'col-12 col-md-5 col-lg-3 form-item--inline';
    $form['field_vacancy_salary_min']['#attributes']['class'][] = $classSalariesRange;
    $form['field_vacancy_salary_max']['#attributes']['class'][] = $classSalariesRange;

    // Actions buttons.
    $form['actions']['submit']['#submit'][] = 'umio_vacancy_node_vacancy_form_submit';
    $form['actions']['submit']['#attributes']['class'] = ['d-none'];
    $form['actions']['decent_work'] = [
      '#type' => 'button',
      '#value' => t('Save'),
      '#name' => 'decent_work_modal',
      '#weight' => 99,
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
   * Function to remove _none option in select.
   *
   * @param array $form
   *   The current form.
   * @param string $field
   *   The field machine name.
   *
   * @return array
   *   Array with the _none option removed from the field.
   */
  private function removeNoneOptionSelect(array $form, string $field): array {
    $options = $form[$field]['widget']['#options'];
    if (isset($options['_none'])) {
      unset($form[$field]['widget']['#options']['_none']);
    }
    $form[$field]['widget']['#options'] = array_merge(['' => $this->t('Select...')], $form[$field]['widget']['#options']);
    $form[$field]['widget']['#attributes'] = [
      'class' => ['select2-without-search'],
    ];

    return $form;
  }

}
