<?php

namespace Drupal\umio_vacancy\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;

/**
 * Provides a confirmation form before clearing out the examples.
 */
class VacancyRejectForm extends AbstractVacancyConfirmForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, string $nid = NULL): array {
    $form = parent::buildForm($form, $form_state, $nid);

    $form['justify'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Comment'),
      '#maxlength' => 500,
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'vacancy_vacancy_reject';
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
    $companyTitle = Node::load($companyNid)->getTitle();
    return $this->t("Before confirm, please justify why the vacancy <span>@title</span>, of type <span>@type</span>, offered by <span>@companyTitle</span>, can’t be approved",
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
    return $this->t('Disapprove vacancy');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Confirm');
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    $vacancy = Node::load($this->nid);
    $status = $vacancy->get('moderation_state')->getString();
    if ($status === 'published') {
      $this->messenger()->addError($this->t("Vacancy approved can't be rejected!"));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $vacancy = Node::load($this->nid);
    $vacancy->set('field_vacancy_justify', $form_state->getValue('justify'));
    $vacancy->set('moderation_state', $this->getNotApprovalStatus());
    $vacancy->setUnpublished();
    $vacancy->save();
    $this->messenger()->addStatus($this->t('Successfully updated!'));
    $form_state->setRedirectUrl($this->getCancelUrl());
  }

}
