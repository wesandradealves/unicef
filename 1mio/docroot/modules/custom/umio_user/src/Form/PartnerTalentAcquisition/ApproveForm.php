<?php

namespace Drupal\umio_user\Form\PartnerTalentAcquisition;

use Drupal\umio_user\UserStatusTrait;

/**
 * Provides a confirmation form to approve partners.
 */
class ApproveForm extends AbstractConfirmForm {

  use UserStatusTrait;

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->t('Do you want to approve the user?');
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'partner_talent_acquisition_approval';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Approve user');
  }

  /**
   * {@inheritdoc}
   */
  protected function getNewStatus(): string {
    return $this->getActiveStatus();
  }

}
