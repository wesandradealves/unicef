<?php

namespace Drupal\umio_vacancy\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a confirmation form before clearing out the examples.
 */
class VacancyDeleteForm extends AbstractVacancyConfirmForm {

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  private $user;

  /**
   * The construct method.
   *
   * @param \Drupal\Core\Session\AccountInterface $user
   *   The current user.
   */
  final public function __construct(AccountInterface $user) {
    $this->user = $user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new static(
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'vacancy_vacancy_delete';
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->t('Are you sure you want to delete this vacancy?');
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Delete vacancy');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $vacancy = Node::load($this->nid);
    $vacancy->set('moderation_state', $this->getDeleteStatus());
    $vacancy->save();
    $this->messenger()->addStatus($this->t('Successfully deleted!'));

    $roles = $this->user->getRoles();
    $redirectUrl = '';
    if (in_array('company_manager', $roles)) {
      $redirectUrl = Url::fromRoute('view.consultancy_vacancy.page_1');
    }
    elseif (in_array('partner_talent_acquisition', $roles)) {
      $redirectUrl = Url::fromRoute('umio_vacancy.index');
    }
    else {
      $redirectUrl = Url::fromRoute('<front>');
    }
    $form_state->setRedirectUrl($redirectUrl);
  }

}
