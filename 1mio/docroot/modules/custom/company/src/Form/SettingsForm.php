<?php

namespace Drupal\company\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * {@inheritdoc}
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'company_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form = parent::buildForm($form, $form_state);
    $config = $this->config('company.settings');

    $form['commitment_term_markup'] = [
      '#type' => 'text_format',
      '#format' => 'full_html',
      '#title' => $this->t('Commitment Term Markup'),
      '#default_value' => $config->get('commitment_term_markup'),
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->config('company.settings')
      ->set('commitment_term_markup', $form_state->getValue('commitment_term_markup')['value'])
      ->save();
    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return [
      'company.settings',
    ];
  }

}
