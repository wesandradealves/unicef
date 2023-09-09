<?php

namespace Drupal\company\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Provides a confirmation form before clearing out the examples.
 */
class StatusDeleteForm extends AbstractStatusForm {

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
  public function getFormId() {
    return 'company_confirm_delete';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to do this?');
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
      $this->companyNode->set('moderation_state', 'canceled');
      $this->companyNode->set('field_deleted_at', date('Y-m-d H:i:s'));
      $this->companyNode->save();

      $this->messenger()->addStatus($this->t('All the information has been removed!'));
    }

    if ($this->getCompanyType() === 'city') {
      $url = new Url('view.view_company.cities');
    }
    else {
      $url = new Url('view.view_company.company_list');
    }

    $form_state->setRedirectUrl($url);
  }

}
