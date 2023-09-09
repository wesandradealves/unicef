<?php

namespace Drupal\company_manager\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\umio_user\Form\UserRegisterFormBase;

/**
 * Form to register a partner talent acquisition user.
 */
class RegisterCompanyManagerForm extends UserRegisterFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'register_company_manager_form';
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
        <span class="page-title-title d-flex align-items-center"><i class="ph-user me-3"></i>@title</span>
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
        $this->t('Company'),
        $this->t('Enter your access data:'),
        $this->t('Your access'),
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
