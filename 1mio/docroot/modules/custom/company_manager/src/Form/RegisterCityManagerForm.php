<?php

namespace Drupal\company_manager\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\umio_user\Form\UserRegisterFormBase;

/**
 * Provides a Company Manager form.
 */
class RegisterCityManagerForm extends UserRegisterFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'company_manager_register_city_manager';
  }

  /**
   * {@inheritdoc}
   */
  protected function getRole(): string {
    return 'partner_talent_acquisition';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form = parent::buildForm($form, $form_state);
    $form['field_name']['#title'] = $this->t('Name');
    $markup = <<<EOT
      <div class="form-page-title">
        <span class="page-title-title d-flex align-items-center"><i class="ph-bank me-3"></i>@title</span>
        <span class="page-title-description">@description</span>
      </div>
      <div class="form-page-info">
        <span class="page-info-icon"><i class="ph-key svg-white"></i></span>
        <span class="page-info-text">@info</span>
      </div>
    EOT;
    $markup = str_replace(
      [
        '@title',
        '@description',
        '@info',
      ],
      [
        $this->t('Public sector'),
        $this->t('Inform your access data:'),
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

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $form_state->setValue('field_flag_public_sector', TRUE);
    parent::submitForm($form, $form_state);
  }

}
