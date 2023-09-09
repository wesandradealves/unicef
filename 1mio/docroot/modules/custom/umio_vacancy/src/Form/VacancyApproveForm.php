<?php

namespace Drupal\umio_vacancy\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;

/**
 * Provides a confirmation form before clearing out the examples.
 */
class VacancyApproveForm extends AbstractVacancyConfirmForm {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'vacancy_vacancy_confirm';
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    $vacancy = Node::load($this->nid);
    $title = $vacancy->getTitle();
    $vacancyTypes = $vacancy->get('field_vacancy_type')->getSetting('allowed_values');
    /** @var \Drupal\Core\Field\FieldItemList $valueType */
    $valueType = $vacancy->field_vacancy_type;
    $type = $vacancyTypes[$valueType->value];
    $companyNid = $vacancy->get('field_vacancy_company')->getValue()[0]['target_id'];
    $companyTitle = '';
    if ($companyNid) {
      $companyTitle = Node::load($companyNid)->getTitle();
    }
    return $this->t('By approving the opportunity, the <span>@title</span> of type <span>@type</span> offered by <span>@companyTitle</span> will be published.',
    [
      '@title' => $title,
      '@type' => $type,
      '@companyTitle' => $companyTitle,
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Approve vacancy');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Yes! Approve');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $vacancy = Node::load($this->nid);
    $vacancy->set('field_vacancy_justify', NULL);
    $vacancy->set('moderation_state', $this->getApprovalStatus());
    $vacancy->save();
    $this->messenger()->addStatus($this->t('Successfully updated!'));
    $form_state->setRedirectUrl($this->getCancelUrl());
  }

}
