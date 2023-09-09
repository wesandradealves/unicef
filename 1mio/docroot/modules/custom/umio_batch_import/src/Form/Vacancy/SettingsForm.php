<?php

namespace Drupal\umio_batch_import\Form\Vacancy;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form to settings batch import vacancy form.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'umio_batch_import_vacancy_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form = parent::buildForm($form, $form_state);
    $config = $this->config('umio_batch_import.vacancy.settings');

    $form['vacancy_template_markup'] = [
      '#type' => 'text_format',
      '#format' => 'full_html',
      '#title' => $this->t('Text before sheet template'),
      '#default_value' => $config->get('vacancy_template_markup'),
      '#required' => TRUE,
    ];

    $form['vacancy_after_submit_markup'] = [
      '#type' => 'text_format',
      '#format' => 'full_html',
      '#title' => $this->t('Text after submit'),
      '#default_value' => $config->get('vacancy_after_submit_markup'),
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->config('umio_batch_import.vacancy.settings')
      ->set('vacancy_template_markup', $form_state->getValue('vacancy_template_markup')['value'])
      ->set('vacancy_after_submit_markup', $form_state->getValue('vacancy_after_submit_markup')['value'])
      ->save();
    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return [
      'umio_batch_import.vacancy.settings',
    ];
  }

}
