<?php

namespace Drupal\umio_batch_import\Form\CityStamp;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form to settings batch import city stamp form.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'umio_batch_import_city_stamp_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form = parent::buildForm($form, $form_state);
    $config = $this->config('umio_batch_import.city_stamp.settings');

    $form['city_stamp_template_markup'] = [
      '#type' => 'text_format',
      '#format' => 'full_html',
      '#title' => $this->t('Text before sheet template'),
      '#default_value' => $config->get('city_stamp_template_markup'),
      '#required' => TRUE,
    ];

    $form['city_stamp_after_submit_markup'] = [
      '#type' => 'text_format',
      '#format' => 'full_html',
      '#title' => $this->t('Text after submit'),
      '#default_value' => $config->get('city_stamp_after_submit_markup'),
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->config('umio_batch_import.city_stamp.settings')
      ->set('city_stamp_template_markup', $form_state->getValue('city_stamp_template_markup')['value'])
      ->set('city_stamp_after_submit_markup', $form_state->getValue('city_stamp_after_submit_markup')['value'])
      ->save();
    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return [
      'umio_batch_import.city_stamp.settings',
    ];
  }

}
