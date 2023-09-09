<?php

namespace Drupal\umio_vacancy\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\ConfirmFormHelper;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\umio_vacancy\VacancyStatusTrait;

/**
 * Provides a confirmation form before clearing out the examples.
 */
abstract class AbstractVacancyConfirmForm extends ConfirmFormBase {

  use VacancyStatusTrait;

  /**
   * Define nid.
   *
   * @var string|null
   */
  protected $nid;

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, string $nid = NULL): array {
    $this->nid = $nid;

    $form['#title'] = $this->getQuestion();

    $form['#attributes']['class'][] = 'confirmation';
    $form['description'] = [
      '#markup' => $this->getDescription(),
    ];
    $form[$this->getFormName()] = [
      '#type' => 'hidden',
      '#value' => 1,
    ];

    $form['actions'] = [
      '#type' => 'actions',
      'submit' => [
        '#type' => 'submit',
        '#value' => $this->getConfirmText(),
        '#button_type' => 'primary',
      ],
      'cancel' => ConfirmFormHelper::buildCancelLink($this, $this->getRequest()),
    ];

    if (!isset($form['#theme'])) {
      $form['#theme'] = 'confirm_form';
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.node.edit_form', ['node' => $this->nid]);
  }

}
