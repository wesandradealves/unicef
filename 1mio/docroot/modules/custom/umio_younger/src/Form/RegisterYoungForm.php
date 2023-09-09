<?php

namespace Drupal\umio_younger\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\umio_user\Form\UserRegisterFormBase;

/**
 * Form to register a young user.
 */
class RegisterYoungForm extends UserRegisterFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'register_young_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getRole(): string {
    return 'young';
  }

  /**
   * Set the weight of the fields in form.
   */
  private function setWeight(array &$form): void {
    $weight = 0;
    $form['field_name']['#weight'] = $weight++;
    $form['field_email']['#weight'] = $weight++;
    $form['field_user_birth_date']['#weight'] = $weight++;
    $form['field_password_confirm']['#weight'] = $weight++;
    $form['field_accepted_term']['#weight'] = $weight++;
    $form['field_CB_user_birth_date']['#weight'] = $weight++;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form = parent::buildForm($form, $form_state);

    $form['field_user_birth_date'] = [
      '#type' => 'date',
      '#title' => $this->t('Birth date'),
      '#required' => TRUE,
      '#default_value' => $form_state->getValue('field_user_birth_date', ''),
    ];

    $form['field_CB_user_birth_date'] = [
      '#type' => 'checkbox',
      '#title' => $this->t(
        'I am under 18 years old and I declare that my parents are aware of my registration on the platform, and that I can register for employability and professional training opportunities aimed at young people aged 16 and over'
      ),
    ];

    $this->setWeight($form);

    $markup = '
      <div class="form-page-title">
        <span class="page-title-title d-flex align-items-center"><i class="ph-user me-3"></i>@title</span>
        <span class="page-title-description">@description</span>
      </div>
      <div class="form-page-info">
        <span class="page-info-icon"><i class="ph-key svg-white"></i></span>
        <span class="page-info-text">@info</span>
      </div>';

    $markup = str_replace(
      [
        '@title',
        '@description',
        '@info',
      ],
      [
        $this->t('Young'),
        $this->t('Enter your account access data:'),
        $this->t('Access data'),
      ],
      $markup
    );
    $form['page_title'] = [
      '#type' => 'markup',
      '#weight' => -1,
      '#markup' => $markup,
    ];

    return $form;
  }

}
