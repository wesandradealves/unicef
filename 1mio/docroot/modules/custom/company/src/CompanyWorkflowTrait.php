<?php

namespace Drupal\company;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\node\NodeInterface;

/**
 * Provides a trait to handle the moderation_state of the company content type.
 */
trait CompanyWorkflowTrait {

  use StringTranslationTrait;

  /**
   * Get the value of active status in moderation_state field.
   *
   * @return string
   *   Value for the moderation_state when the company is active.
   */
  public function getApprovalStatus(): string {
    return 'published';
  }

  /**
   * Get the value of not approval status in moderation_state field.
   *
   * @return string
   *   Value for the moderation_state when the company is not approved.
   */
  public function getNotApprovalStatus(): string {
    return 'not_approved';
  }

  /**
   * Get the value of waiting approval status in moderation_state field.
   *
   * @return string
   *   Value for the moderation_state when the company is waiting for approval.
   */
  public function getWaitingApprovalStatus(): string {
    return 'draft';
  }

  /**
   * Get the value of deleted status in moderation_state field.
   *
   * @return string
   *   Value for the moderation_state when the company is deleted.
   */
  public function getDeleteStatus(): string {
    return 'canceled';
  }

  /**
   * Function to get the current value of the moderation_state field.
   *
   * @param \Drupal\node\NodeInterface $company
   *   The company entity.
   *
   * @return string|null
   *   The value of the field moderation_state for the company.
   */
  public function getCompanyStatus(NodeInterface $company): ?string {
    $status = $company->get('moderation_state')->getString();
    if (!$status) {
      return NULL;
    }

    return $status;
  }

  /**
   * Function to return attributes of the status.
   *
   * @return array
   *   Array with the attributes of the status.
   */
  public function getStatusAttributes(): array {
    return [
      'published' => [
        'icon' => 'ph-check',
        'class' => 'text-success',
        'text' => $this->t('Active'),
      ],
      'not_approved' => [
        'icon' => 'ph-shield-warning',
        'class' => 'text-danger',
        'text' => $this->t('Not approved'),
      ],
      'draft' => [
        'icon' => 'ph-clock',
        'class' => 'text-warning',
        'text' => $this->t('Waiting approve'),
      ],
      'pending_approval' => [
        'icon' => 'ph-clock',
        'class' => 'text-warning',
        'text' => $this->t('Waiting approve'),
      ],
      'canceled' => [
        'icon' => 'ph-trash',
        'class' => 'text-danger',
        'text' => $this->t('Deleted'),
      ],
    ];
  }

}
