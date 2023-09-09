<?php

namespace Drupal\company\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\umio_user\UserStatusTrait;
use Drupal\user\Entity\User;

/**
 * Provides a confirmation form before clearing out the examples.
 */
class StatusApproveForm extends AbstractStatusForm {

  use UserStatusTrait;

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, string $nid = NULL) {
    $form = parent::buildForm($form, $form_state, $nid);

    $form['actions']['submit']['#attributes']['class'] = [
      'custom-cancel',
    ];
    unset($form['actions']['cancel']);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    if ($this->getCompanyType() === 'city') {
      return $this->t("Do you confirm the city approval?");
    }
    return $this->t('Do you confirm the company approval?');
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'view_customization_status_confirm';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    if ($this->getCompanyType() === 'city') {
      return $this->t('Approve city');
    }
    return $this->t('Approve company');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    if ($this->getCompanyType() === 'city') {
      return new Url('view.view_company.cities');
    }

    return new Url('view.view_company.company_list');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    if ($this->companyNode) {
      $unicefStamp = $this->companyNode->get('field_company_unicef_stamp');
      $commitmentTerm = $this->companyNode->get('field_company_commitment_term');
      if ($commitmentTerm->getValue() == NULL && $unicefStamp->getString() != 1) {
        $this->messenger()->addMessage($this->t('It is impossible to change the Company status without the commitment term.'), 'error');
      }
      else {
        $this->companyNode->set('moderation_state', 'published');
        $user = User::load($this->companyNode->getOwnerId());
        $user->set('field_user_status', $this->getActiveStatus());
        $user->save();
        $this->companyNode->save();
        $this->messenger()->addStatus($this->t('Successfully updated!'));
      }
    }
    $form_state->setRedirectUrl($this->getCancelUrl());
  }

}
