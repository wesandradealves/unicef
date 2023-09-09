<?php

namespace Drupal\company\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;

/**
 * Provides a confirmation form before clearing out the examples.
 */
class StatusRejectForm extends AbstractStatusForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, string $nid = NULL) {
    $form = parent::buildForm($form, $form_state, $nid);
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
    if ($this->getCompanyType() === 'city') {
      return $this->t("Do you confirm the city block?");
    }
    return $this->t("Do you confirm the company's block?");
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'view_customization_status_reject';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    if ($this->getCompanyType() === 'city') {
      return $this->t('Block city');
    }
    return $this->t('Block company');
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
      $this->companyNode->set('field_company_justify', $form_state->getValue('justify'));
      $this->companyNode->set('moderation_state', 'not_approved');
      $this->companyNode->setUnpublished();
      $this->companyNode->save();

      $branchUids = \Drupal::entitytypemanager()
        ->getStorage('node')
        ->getQuery()
        ->condition('type', 'company')
        ->condition('field_company_main_office', $this->companyNode->id())
        ->execute();
      if (is_array($branchUids) && count($branchUids)) {
        foreach ($branchUids as $branchUid) {
          $branchCompany = Node::load($branchUid);
          // Set the basic data from the main company to the branches.
          $branchCompany->set('moderation_state', 'not_approved');
          $branchCompany->setUnpublished();
          $branchCompany->save();
        }
      }

      $this->sendCompanyBlockJustify($form_state->getValue('justify'));

      $this->messenger()->addStatus($this->t('Successfully updated!'));
    }
    $form_state->setRedirectUrl($this->getCancelUrl());
  }

}
