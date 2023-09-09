<?php

namespace Drupal\umio_user\Form\PartnerTalentAcquisition;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\umio_user\UserStatusTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a confirmation form to approve partners.
 */
abstract class AbstractConfirmForm extends ConfirmFormBase {

  use UserStatusTrait;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * User ID.
   *
   * @var string|null
   */
  protected $uid;

  /**
   * Partner user.
   *
   * @var \Drupal\user\Entity\User|null
   */
  protected $user;

  /**
   * The construct method.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   */
  final public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * Function to return the new status to set to the user.
   *
   * @return string
   *   Status to set to the user.
   */
  abstract protected function getNewStatus(): string;

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return Url::fromRoute('umio_admin_area.ta_admin.company_people');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, string $uid = NULL) {
    $this->uid = $uid;
    /** @var \Drupal\user\Entity\User|null */
    $user = $this->entityTypeManager->getStorage('user')->load($uid);
    $this->user = $user;
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    if (!$this->user) {
      $this->messenger()->addError($this->t('User not found.'));
    }
    else {
      $status = $this->getUserStatus($this->user);
      if ($status) {
        if ($status === $this->getNewStatus()) {
          if ($status === $this->getActiveStatus()) {
            $this->messenger()->addError($this->t('User already active!'));
          }
          elseif ($status === $this->getBlockedStatus()) {
            $this->messenger()->addError($this->t('User already blocked!'));
          }
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->user->set('field_user_status', $this->getNewStatus());
    $this->user->save();
    $form_state->setRedirectUrl($this->getCancelUrl());
  }

}
