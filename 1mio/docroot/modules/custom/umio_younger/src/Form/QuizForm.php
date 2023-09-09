<?php

namespace Drupal\umio_younger\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\umio_younger\Service\QuizService;
use Drupal\user\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form to register the skills in the young user.
 */
class QuizForm extends FormBase {

  /**
   * The number of options that the user has to selected.
   *
   * @var int
   */
  private const NUMBER_OPTIONS = 4;

  /**
   * The quiz service.
   *
   * @var \Drupal\umio_younger\Service\QuizService
   */
  private $quizService;

  /**
   * The construct method.
   *
   * @param \Drupal\umio_younger\Service\QuizService $quizService
   *   The current user.
   */
  final public function __construct(QuizService $quizService) {
    $this->quizService = $quizService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new static(
      $container->get('umio_younger.quiz_service')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'quiz_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form['#attached']['library'][] = 'umio_helpers/umio_helpers.checkbox_cards';
    $terms = $this->quizService->getTerms();

    // Randomize the terms.
    shuffle($terms);
    $options = [];
    foreach ($terms as $term) {
      $options[$term->id()] = $term->label();
    }

    $form['skills'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Skills 1MIO'),
      '#title_display' => 'invisible',
      '#options' => $options,
      '#error_no_message' => TRUE,
      '#attributes' => [
        'class' => ['checkbox_cards'],
      ],
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    parent::validateForm($form, $form_state);
    $values = $form_state->getValues();
    if (isset($values['skills'])) {
      $skills = $values['skills'];
      $counter = 0;
      foreach ($skills as $skill) {
        if ($skill !== 0) {
          $counter++;
        }
      }

      if ($counter !== self::NUMBER_OPTIONS) {
        $form['#attributes']['class'][] = 'error';
        $form_state->setErrorByName('skills', $this->t('Please select 4 options from those listed below.'));
      }
    }
    else {
      $form['#attributes']['class'][] = 'error';
      $form_state->setErrorByName('skills', $this->t('Please select 4 options from those listed below.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $user = User::load($this->currentUser()->id());
    $terms = $this->quizService->getTerms();

    $values = $form_state->getValues();
    $weights = [
      1 => 0,
      2 => 0,
      3 => 0,
      4 => 0,
    ];
    foreach ($values['skills'] as $skill) {
      if ($skill !== 0) {
        $term = $terms[$skill];
        $weights[1] += $term->get('field_weight_skill_1')->getString();
        $weights[2] += $term->get('field_weight_skill_2')->getString();
        $weights[3] += $term->get('field_weight_skill_3')->getString();
        $weights[4] += $term->get('field_weight_skill_4')->getString();
      }
    }

    $user->set('field_younger_skills', $this->getHighestValues($weights));
    $user->save();

    $form_state->setRedirect('umio_younger.young_skills');
  }

  /**
   * Get the highest values in the array.
   *
   * @param array $weights
   *   The array with the values of the skill weights.
   *
   * @return array
   *   Array with the highest values in the weights array.
   */
  private function getHighestValues(array $weights): array {
    $maxWeights = [];
    $max = max($weights);
    $maxKey = array_search($max, $weights);
    $maxWeights[] = $maxKey;
    unset($weights[$maxKey]);

    // If has another key with the same max value.
    if (array_search($max, $weights)) {
      $max = max($weights);
      $maxKey = array_search($max, $weights);
      $maxWeights[] = $maxKey;
    }

    return $maxWeights;
  }

}
