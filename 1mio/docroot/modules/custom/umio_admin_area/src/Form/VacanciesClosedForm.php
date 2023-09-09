<?php

namespace Drupal\umio_admin_area\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\umio_admin_area\Service\VacanciesClosedService;
use Drupal\user\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a VacanciesClosedForm.
 */
class VacanciesClosedForm extends FormBase {

  /**
   * The name of the field that save the number of vacancies closed.
   *
   * @var string
   */
  private const VACANCY_CLOSED_FIELD = 'field_user_vacancies_closed';

  /**
   * The field of the paragraph.
   *
   * @var array
   */
  private const PARAGRAPH_FIELDS = [
    'field_disabilities_youngs' => 166,
    'field_younger_mothers' => 186,
    'field_socio_educational_system' => 181,
    'field_ethnic_racial_equity' => 131,
    'field_lgbtia' => 171,
    'field_location' => 141,
    'field_girls_science' => 176,
    'field_originating_populations' => 146,
    'field_low_income' => 136,
    'field_child_labor_victims' => 191,
  ];

  /**
   * The entity type manager.
   *
   * @var \Drupal\umio_admin_area\Service\VacanciesClosedService
   */
  private $vacanciesClosed;

  /**
   * The constructor.
   *
   * @param \Drupal\umio_admin_area\Service\VacanciesClosedService $vacanciesClosed
   *   The entity type manager.
   */
  final public function __construct(VacanciesClosedService $vacanciesClosed) {
    $this->vacanciesClosed = $vacanciesClosed;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new static(
      $container->get('umio_admin_area.vacancies_closed'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'vacancies_closed_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $user = User::load($this->currentUser()->id());

    $form[self::VACANCY_CLOSED_FIELD] = [
      '#type' => 'number',
      '#title' => $this->t('Closed vacancy'),
      '#description' => $this->t('Enter the number of vacancies you have filled'),
      '#description_display' => 'before',
      '#default_value' => $user->get(self::VACANCY_CLOSED_FIELD)->getString(),
      '#min' => 0,
      '#required' => TRUE,
    ];

    $form['priority_profiles'] = $this->getPriorityProfileFields();

    $form['actions']['next'] = [
      '#type' => 'submit',
      '#button_type' => 'primary',
      '#value' => $this->t('Update'),
    ];

    return $form;
  }

  /**
   * Get the priority profile fields.
   *
   * @return array
   *   The priority profiles fields.
   */
  private function getPriorityProfileFields(): array {
    $user = User::load($this->currentUser()->id());
    if (!$user) {
      return [];
    }

    $userValues = $this->vacanciesClosed->getVacanciesClosedByTerm($user);
    $terms = $this->vacanciesClosed->getPriorityProfileTerms();

    $priorityProfilesFields = [];
    foreach ($terms as $term) {
      $fieldName = array_search($term->id(), self::PARAGRAPH_FIELDS);
      if ($fieldName !== FALSE) {
        $priorityProfilesFields[$fieldName] = [
          '#type' => 'number',
          '#title' => $term->label(),
          '#min' => 0,
        ];
        if (isset($userValues[$fieldName])) {
          $priorityProfilesFields[$fieldName]['#default_value'] = $userValues[$fieldName];
        }
      }
    }

    return $priorityProfilesFields;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    parent::validateForm($form, $form_state);
    foreach ($form_state->getValues() as $key => $value) {
      if ($key === self::VACANCY_CLOSED_FIELD) {
        if ($value <= 0) {
          $form_state->setErrorByName(self::VACANCY_CLOSED_FIELD, $this->t('Please enter a value greater than 0'));
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $user = User::load($this->currentUser()->id());
    if (!$user) {
      return;
    }

    $paragraphs = $this->vacanciesClosed->getPriorityProfileParagraphs($user);
    foreach ($paragraphs as $entity) {
      $entity->delete();
    }

    $values = $form_state->getValues();
    if (isset($values[self::VACANCY_CLOSED_FIELD])) {
      $user = User::load($this->currentUser()->id());
      $user->set(self::VACANCY_CLOSED_FIELD, $values[self::VACANCY_CLOSED_FIELD]);
      $user->save();
    }

    $paragraph = Paragraph::create([
      'type' => 'counter_priority_profiles',
      'field_disabilities_youngs' => $values['field_disabilities_youngs'],
      'field_younger_mothers' => $values['field_younger_mothers'],
      'field_socio_educational_system' => $values['field_socio_educational_system'],
      'field_ethnic_racial_equity' => $values['field_ethnic_racial_equity'],
      'field_lgbtia' => $values['field_lgbtia'],
      'field_location' => $values['field_location'],
      'field_girls_science' => $values['field_girls_science'],
      'field_originating_populations' => $values['field_originating_populations'],
      'field_low_income' => $values['field_low_income'],
      'field_child_labor_victims' => $values['field_child_labor_victims'],
    ]);
    $paragraph->save();
    $paragraphCreated = [];
    $paragraphCreated[] = [
      'target_id' => $paragraph->id(),
      'target_revision_id' => $paragraph->getRevisionId(),
    ];
    $user->set('field_vacancies_priority_profile', $paragraphCreated);
    $user->save();
  }

}
