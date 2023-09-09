<?php

namespace Drupal\umio_vacancy;

use Drupal\node\Entity\Node;

/**
 * Provides a trait to handle the moderation_state of the vacancy content type.
 */
trait VacancyStatusTrait {

  /**
   * Get the value of active status in moderation_state field.
   *
   * @return string
   *   Value for the moderation_state when the vacancy is active.
   */
  public function getApprovalStatus(): string {
    return 'published';
  }

  /**
   * Get the value of not approval status in moderation_state field.
   *
   * @return string
   *   Value for the moderation_state when the vacancy is not approved.
   */
  public function getNotApprovalStatus(): string {
    return 'not_approved';
  }

  /**
   * Get the value of waiting approval status in moderation_state field.
   *
   * @return string
   *   Value for the moderation_state when the vacancy is waiting for approval.
   */
  public function getWaitingApprovalStatus(): string {
    return 'draft';
  }

  /**
   * Get the value of deleted status in moderation_state field.
   *
   * @return string
   *   Value for the moderation_state when the vacancy is deleted.
   */
  public function getDeleteStatus(): string {
    return 'canceled';
  }

  /**
   * Function to get the current value of the moderation_state field.
   *
   * @param \Drupal\node\Entity\Node $vacancy
   *   The vacancy entity.
   *
   * @return string|null
   *   The value of the field moderation_state for the vacancy.
   */
  public function getVacancyStatus(Node $vacancy): ?string {
    $status = $vacancy->get('moderation_state')->getString();
    if (!$status) {
      return NULL;
    }

    return $status;
  }

}
