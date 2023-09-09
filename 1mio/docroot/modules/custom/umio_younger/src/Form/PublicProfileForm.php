<?php

namespace Drupal\umio_younger\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\umio_younger\Service\YoungFieldsService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a umio_younger form.
 */
class PublicProfileForm extends FormBase {

  /**
   * Define the youngFieldsService.
   *
   * @var \Drupal\umio_younger\Service\YoungFieldsService
   */
  protected $youngFieldsService;

  /**
   * {@inheritdoc}
   */
  final public function __construct(YoungFieldsService $youngFieldsService) {
    $this->youngFieldsService = $youngFieldsService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new static(
      $container->get('umio_younger.young_fields')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'umio_younger_public_profile';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, int $user = NULL) {
    // Make form configurations.
    $this->youngFieldsService->setFormUser($user);
    $form = $this->youngFieldsService->createPublicProfileFieldsForm($form, $form_state);

    $form['actions'] = [
      '#type' => 'actions',
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $user = $form_state->get('entity');

    $form_display = $form_state->get('form_display');
    $form_display->extractFormValues($user, $form, $form_state);

    if ($user->save()) {
      \Drupal::messenger()->addMessage($this->t('Submitted Successfully!'), 'status', TRUE);
    }
  }

}
