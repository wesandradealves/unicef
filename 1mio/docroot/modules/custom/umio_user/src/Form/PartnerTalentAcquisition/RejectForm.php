<?php

namespace Drupal\umio_user\Form\PartnerTalentAcquisition;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\umio_user\UserStatusTrait;

/**
 * Provides a confirmation form to reject partners.
 */
class RejectForm extends AbstractStatusTAForm {

  use UserStatusTrait;

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, string $uid = NULL) {
    $form = parent::buildForm($form, $form_state, $uid);
    $form['justify'] = [
      '#type' => 'textarea',
      '#title' => $this->t('What is the reason for blocking?'),
      '#label_attributes' => [
        'class' => ['form-required'],
      ],
      '#attributes' => [
        'required' => 'required',
      ],
    ];
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
    return $this->t('Do you want to reject the user?');
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'partner_talent_acquisition_reject';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Reject user');
  }

  /**
   * {@inheritdoc}
   */
  protected function getNewStatus(): string {
    return $this->getBlockedStatus();
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('umio_user.people_profile', ['uid' => $this->user->id()]);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->user->set('field_user_status', $this->getNewStatus());
    $this->user->save();
    $this->sendUserBlockJustify($form_state->getValue('justify'));
    $this->messenger()->addStatus($this->t('Successfully updated!'));
    $form_state->setRedirectUrl($this->getCancelUrl());
  }

}
