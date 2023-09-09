<?php

namespace Drupal\company\Service;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Url;
use Drupal\node\NodeInterface;
use Drupal\company\CompanyWorkflowTrait;

/**
 * Service with helpers function to handle with company content type.
 */
class CompanyWorkflowService {

  use CompanyWorkflowTrait;

  /**
   * Get the field approve and reject workflow fields.
   *
   * @param \Drupal\node\NodeInterface $company
   *   The company to generate the workflows fields.
   *
   * @return array
   *   Return an array (nid) with all companies on the organization structure.
   */
  public static function getApproveAndRejectWorkflowFields(NodeInterface $company): array {
    $fields = [];

    $negated_moderation_state = [
      'published',
      'not_approved',
    ];
    $moderation_sate = $company->get('moderation_state')->getValue();
    if (isset($moderation_sate[0]['value']) &&
        (!in_array($moderation_sate[0]['value'], $negated_moderation_state)) &&
        (!$company->get('field_deleted_at')->getValue())) {

      $url = Url::fromRoute('company.status_reject', ['nid' => $company->id()]);
      $fields['reject'] = [
        '#type' => 'link',
        '#title' => t('Disapprove'),
        '#route_name' => 'company.status_reject',
        '#url' => $url,
        '#weight' => 100,
        '#attributes' => [
          'class' => [
            'use-ajax',
            'btn',
            'btn-danger',
            'text-bold',
            'header-btn-sizes',
          ],
          'data-dialog-type' => 'modal',
          'data-dialog-options' => Json::encode([
            'dialogClass' => 'modal-drupal-dialog',
          ]),
        ],
      ];

      if (self::checkCommitmentTerm($company)) {
        $url = Url::fromRoute('company.status_confirm', ['nid' => $company->id()]);
        $fields['approve'] = [
          '#type' => 'link',
          '#title' => t('Approve'),
          '#route_name' => 'company.status_confirm',
          '#url' => $url,
          "#weight" => 100,
          '#attributes' => [
            'class' => [
              'use-ajax',
              'btn',
              'btn-success',
              'text-bold',
              'header-btn-sizes',
            ],
            'data-dialog-type' => 'modal',
            'data-dialog-options' => Json::encode([
              'dialogClass' => 'modal-drupal-dialog',
            ]),
          ],
        ];
      }

    }

    return $fields;
  }

  /**
   * Get the delete workflow field.
   *
   * @param \Drupal\node\NodeInterface $company
   *   The company to generate the workflows fields.
   *
   * @return array
   *   Return an array (nid) with all companies on the organization structure.
   */
  public function getDeleteWorkflowField(NodeInterface $company): array {
    $fields = [];
    if (!$company->get('field_deleted_at')->getValue()) {
      $url = Url::fromRoute('company.company_confirm_delete', ['nid' => $company->id()]);
      $fields = [
        '#type' => 'link',
        '#title' => t('Delete'),
        '#route_name' => 'company.company_confirm_delete',
        '#url' => $url,
        "#weight" => 100,
        '#attributes' => [
          'class' => ['use-ajax'],
          'data-dialog-type' => 'modal',
          'data-dialog-options' => Json::encode([
            'dialogClass' => 'modal-drupal-dialog',
          ]),
        ],
      ];
    }
    return $fields;
  }

  /**
   * Get the fields for moderation state and created field.
   *
   * @param \Drupal\node\NodeInterface $company
   *   The company to generate the workflows fields.
   *
   * @return array
   *   Return an array (nid) with all companies on the organization structure.
   */
  public function getModerationStateAndCreatedAtFields(NodeInterface $company): array {
    $moderationState = $this->getCompanyStatus($company);
    $fields = [];
    if ($moderationState) {
      $status = $this->getStatusAttributes()[$moderationState];
      $fields['moderation_state'] = [
        '#type' => 'markup',
        '#markup' => "<div class='" . $status['class'] . "' ><i class='" . $status['icon'] . "'></i>" . $status['text'] . "</div>",
      ];
      $created = $company->created->getValue()[0]['value'];
      $date = new \DateTime();
      $created = $date->setTimestamp($created)->format('d/m/Y');
      $translatePostedIn = $this->t('Posted in:');
      $fields['created_at'] = [
        '#type' => 'markup',
        '#markup' => '<div class="content-date-created">' . $translatePostedIn . $created . '</div>',
      ];
    }

    return $fields;
  }

  /**
   * Check if company has commitment term.
   *
   * @param \Drupal\node\NodeInterface $company
   *   The company to generate the workflows fields.
   *
   * @return bool
   *   Return bool to check if has commitment term.
   */
  private static function checkCommitmentTerm(NodeInterface $company): bool {
    $commitmentTerm = $company->get('field_company_commitment_term')->getValue();
    $companyUnicefStamp = $company->get('field_company_unicef_stamp')->getValue();
    if (!$commitmentTerm && $companyUnicefStamp == NULL) {
      return FALSE;
    }

    return TRUE;
  }

}
