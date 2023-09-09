<?php

namespace Drupal\umio_user;

use Drupal\user\Entity\User;

/**
 * Provides a trait to handle the field_user_status field.
 */
trait UserStatusTrait {

  /**
   * Get the value of active status in field_user_status field.
   *
   * @return string
   *   Value for the field_user_status when the user is active.
   */
  public function getActiveStatus(): string {
    return 'Ativo';
  }

  /**
   * Get the value of blocked status in field_user_status field.
   *
   * @return string
   *   Value for the field_user_status when the user is blocked.
   */
  public function getBlockedStatus(): string {
    return 'Bloqueado';
  }

  /**
   * Get the value of waiting approval status in field_user_status field.
   *
   * @return string
   *   Value for the field_user_status when the user is waiting for approval.
   */
  public function getWaitingApprovalStatus(): string {
    return 'Aguardando Ativação';
  }

  /**
   * Function to get the current value of the field_user_status field.
   *
   * @param \Drupal\user\Entity\User $user
   *   The user entity.
   *
   * @return string|null
   *   The value of the field field_user_status for the user.
   */
  public function getUserStatus(User $user): ?string {
    $status = $user->get('field_user_status')->getValue();
    if (!$status) {
      return NULL;
    }

    return $status[0]['value'];
  }

}
