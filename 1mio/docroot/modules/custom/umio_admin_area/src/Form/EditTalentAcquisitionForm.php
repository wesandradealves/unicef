<?php

namespace Drupal\umio_admin_area\Form;

use Drupal\comment_notify\UserNotificationSettings;
use Drupal\company_manager\Service\TalentAcquisitionFields;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\umio_helpers\Service\FormValidator;
use Drupal\user\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a Talent Acquisition edit form.
 */
class EditTalentAcquisitionForm extends FormBase {

  use DependencySerializationTrait;

  /**
   * Define the FormValidator.
   *
   * @var \Drupal\umio_helpers\Service\FormValidator
   */
  protected $formValidator;

  /**
   * The account object.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $user;

  /**
   * Comment notify service.
   *
   * @var \Drupal\comment_notify\UserNotificationSettings
   */
  protected $userSettings;

  /**
   * Define the TalentAcquisitionFields.
   *
   * @var \Drupal\company_manager\Service\TalentAcquisitionFields
   */
  protected $talentAcquisitionFieldsService;

  /**
   * {@inheritdoc}
   */
  final public function __construct(
    AccountInterface $user,
    FormValidator $formValidator,
    UserNotificationSettings $userSettings,
    TalentAcquisitionFields $talentAcquisitionFields
  ) {
    $this->user = $user;
    $this->formValidator = $formValidator;
    $this->userSettings = $userSettings;
    $this->talentAcquisitionFieldsService = $talentAcquisitionFields;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new static(
      $container->get('current_user'),
      $container->get('umio_helpers.form_validator'),
      $container->get('comment_notify.user_settings'),
      $container->get('company_manager.talent_acquisition_fields'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'company_manager_edit_talent_acquisition';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $this->talentAcquisitionFieldsService->setFormUser($this->user->id());

    $form = $this->talentAcquisitionFieldsService->createTalentAcquisitionFieldsDisplay($form, $form_state);

    $this->defineGeneralCustomizations($form);

    if ($this->userSettings->getSettings($this->user->id())) {
      $notifySettings = $this->userSettings->getSettings($this->user->id());
    }
    else {
      $notifySettings = $this->userSettings->getDefaultSettings();
    }

    $form['field_user_comment_notify'] = [
      '#type' => 'checkbox',
      '#title' => t('Receber notificações de comentários'),
      '#default_value' => $notifySettings['comment_notify'] ?? NULL,
      '#attributes' => ['class' => ['comment-notify']],
      '#weight' => 99,
      '#required' => FALSE,
    ];

    $this->defineFieldsWeigths($form);
    $this->defineFormButtons($form);

    return $form;
  }

  /**
   * Define the form buttons.
   *
   * @param array $form
   *   The $form array of the application.
   */
  private function defineFormButtons(array &$form): void {

    $form['actions'] = [
      '#type' => 'actions',
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
    ];

  }

  /**
   * Set fields value in Talen Acquisiton.
   *
   * @param array $form
   *   Define the form variable array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Define the $form_state variable.
   */
  public function setDefaultValues(array $form, FormStateInterface $form_state): void {
    $currentUser = User::load($this->user->id());
    $form_state->setValue('field_opening', 'edit');
    $form_state->setValue('field_name', $currentUser->get('name')->getString());
    $form_state->setValue('field_email', $currentUser->get('mail')->getString());
    $form_state->setValue('field_company_role', $currentUser->get('field_user_company_role')->getString());
    $form_state->setValue('field_company_role_started', $currentUser->get('field_user_company_role_started')->getString());
    $form_state->setValue('field_telephone', $currentUser->get('field_user_telephone')->getString());
    $form_state->setValue('field_telephone_extension', $currentUser->get('field_user_telephone_extension')->getString());
    $form_state->setValue('field_phone', $currentUser->get('field_user_phone')->getString());

    if ($currentUser->get('field_user_address')->getValue() != NULL) {

      $addressParagraph = Paragraph::load($currentUser->get('field_user_address')->getValue()[0]['target_id']);
      $address = $addressParagraph->get('field_paragraph_address')->getValue()[0];
      $form_state->setValue('administrative_area', $address['administrative_area']);
      $form_state->setValue('locality', $address['locality']);

    }

  }

  /**
   * Make some display changes on the form and call the fields customizations.
   *
   * @param array $form
   *   The $form array of the application.
   */
  private function defineGeneralCustomizations(array &$form): void {

    $form['#prefix'] = '<div class= "edit-young-form-region-content">';
    $form['#suffix'] = '</div>';

    // Define the form customizations functions general and calls.
    $this->defineFieldsCustomizations($form);

  }

  /**
   * Make some display changes on the form fields.
   *
   * @param array $form
   *   The $form array of the application.
   */
  private function defineFieldsCustomizations(array &$form): void {

    $form['field_user_profile_picture']['#title_display'] = 'invisible';
    $form['field_user_profile_picture']['#description'] = '';

    $form['field_user_company_role_started']['widget']['#attributes']['class'] = ['form-control'];

    $form['field_user_telephone']['widget'][0]['value']['#attributes']['class'] = ['telephone-mask'];
    $form['field_user_telephone_extension']['widget'][0]['value']['#attributes']['class'] = ['telephone-extension-mask'];
    $form['field_user_phone']['widget'][0]['value']['#attributes']['class'] = ['phone-mask'];

    $form['field_user_company_role_started']['widget'][0]['value']['#title'] = $this->t('Started in');

    $form['field_user_company_role']['#prefix'] = '<div class="row"><div class="col-7">';
    $form['field_user_company_role']['#suffix'] = '</div>';
    $form['field_user_company_role_started']['#prefix'] = '<div class="col-5">';
    $form['field_user_company_role_started']['#suffix'] = '</div></div>';

    $form['field_user_telephone']['#prefix'] = '<div class="row"><div class="col-7">';
    $form['field_user_telephone']['#suffix'] = '</div>';
    $form['field_user_telephone_extension']['#prefix'] = '<div class="col-5">';
    $form['field_user_telephone_extension']['#suffix'] = '</div></div>';

    if (isset($form['field_user_professional_traject']['widget'][0]['top']['links']['remove_button'])) {
      $form['field_user_professional_traject']['widget'][0]['top']['links']['remove_button'] = [];
    }

    $paragraphRemoveInfos = [
      'field_user_address',
      'field_user_professional_traject',
    ];

    foreach ($paragraphRemoveInfos as $fieldToRemoveParagraphInfo) {
      if (isset($form[$fieldToRemoveParagraphInfo]['widget'][0]['info'])) {
        $form[$fieldToRemoveParagraphInfo]['widget'][0]['info'] = [];
      }
    }

    // Define the curriculum customizations.
    if (isset($form['field_user_professional_traject']['widget'][0]['subform'])) {
      $this->definetrajectoryCustomizations($form);
    }

  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    $telephone = $form_state->getValue('field_telephone');
    if ($telephone && !$this->formValidator->validateTelephone($telephone)) {
      $form_state->setErrorByName('field_telephone', $this->t('This field must be a valid telephone.'));
    }

    $telephoneExtension = $form_state->getValue('field_telephone_extension');
    if ($telephoneExtension && !is_numeric($telephoneExtension)) {
      $form_state->setErrorByName('field_telephone_extension', $this->t('This field must be a valid telephone extension.'));
    }

    $phone = $form_state->getValue('field_phone');
    if ($phone && !$this->formValidator->validatePhone($phone)) {
      $form_state->setErrorByName('field_phone', $this->t('This field must be a valid phone.'));
    }

    $date_started = $form_state->getValue('field_user_company_role_started')[0]['value'];
    if ($date_started !== NULL) {
      $date_started->format('Y-m-d h::i::s');
      if ($date_started > date('Y-m-d')) {
        $form_state->setErrorByName('field_user_company_role_started', $this->t('This date cannot be later than today.'));
      }
    }

  }

  /**
   * Make some display changes on the form fields.
   *
   * @param \Drupal\user\Entity\User $user
   *   The current user of the form.
   * @param array $data
   *   The data catched in the form.
   */
  private function saveBasicData(User &$user, array $data): void {
    $field_user_profile_picture = $data['field_user_profile_picture'] ? $data['field_user_profile_picture'] : NULL;
    $field_user_telephone_extension = $data['field_user_telephone_extension'] ? $data['field_user_telephone_extension'] : NULL;

    $user->set('field_user_profile_picture', $field_user_profile_picture);
    $user->set('field_user_telephone_extension', $field_user_telephone_extension);

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $data = $form_state->getValues();
    $user = $form_state->get('entity');

    $this->saveBasicData($user, $data);

    $form_display = $form_state->get('form_display');
    $form_display->extractFormValues($user, $form, $form_state);

    $this->userSettings->saveSettings($this->user->id(), NULL, $form_state->getValue('field_user_comment_notify'));

    if ($user->save()) {
      \Drupal::messenger()->addMessage($this->t('Form Submitted Successfully'), 'status', TRUE);
    }
  }

  /**
   * Make some changes on the form fields to adjust the position of the fields.
   *
   * @param array $form
   *   The $form array of the application.
   */
  private function defineFieldsWeigths(array &$form): void {
    // Define the form field weight.
    $weight = 0;

    $form['field_user_profile_picture']['#weight'] = ++$weight;
    $form['field_user_name']['#weight'] = ++$weight;
    $form['field_user_email']['#weight'] = ++$weight;

    $form['field_user_company_role']['#weight'] = ++$weight;
    $form['field_user_company_role_started']['#weight'] = ++$weight;

    $form['field_user_telephone']['#weight'] = ++$weight;
    $form['field_user_telephone_extension']['#weight'] = ++$weight;

    $form['field_user_phone']['#weight'] = ++$weight;

    $form['field_user_address']['#weight'] = ++$weight;

    $form['field_user_professional_traject']['#weight'] = ++$weight;
    $form['field_user_social']['#weight'] = ++$weight;

    $form['actions']['#weight'] = ++$weight;
  }

  /**
   * Make the form group title object.
   *
   * @param array $form
   *   The $form array of the application.
   * @param string $group_name
   *   The group field name on the form.
   * @param \Drupal\Core\StringTranslation\TranslatableMarkup $title
   *   The group title translated.
   * @param \Drupal\Core\StringTranslation\TranslatableMarkup $description
   *   The group description translated.
   * @param string $tag
   *   The heading type.
   */
  private function defineGroupTitle(array &$form, string $group_name, TranslatableMarkup $title, TranslatableMarkup $description = NULL, string $tag = 'h3'): void {

    $titleText = (string) $title;

    $markup = "<div class=\"edit-young--form--group--title\">
      <{$tag} class=\"edit-young--form--group--title--text\">" . $titleText . "</{$tag}>
    </div>";

    if ($description) {
      $descriptionText = (string) $description;
      $markup .= "<span class=\"text-muted d-block\">{$descriptionText}</span>";
    }

    $form[$group_name] = [
      '#type' => 'markup',
      '#parents' => ['form'],
      '#markup' => $markup,
    ];
  }

  /**
   * Make some display changes on the form fields.
   *
   * @param array $form
   *   The $form array of the application.
   */
  public function definetrajectoryCustomizations(array &$form): void {

    $trajectoryConfigWithPrefix = [
      'prefix' => '<div class="d-md-flex justify-content-between align-items-center">',
      'suffix' => '',
    ];
    $trajectoryConfigWithSuffix = [
      'prefix' => '',
      'suffix' => '</div>',
    ];
    $trajectoryFieldNames = [
      'field_professional_trajectory' => [
        'field_experience_begin' => $trajectoryConfigWithPrefix,
        'field_experience_current' => $trajectoryConfigWithSuffix,
      ],
      'field_educational_training' => [
        'field_education_begin' => $trajectoryConfigWithPrefix,
        'field_education_current' => $trajectoryConfigWithSuffix,
      ],
      'field_volunteer_work' => [
        'field_volunteer_work_begin' => $trajectoryConfigWithPrefix,
        'field_volunteer_work_end' => $trajectoryConfigWithSuffix,
      ],
    ];
    $trajectoryFieldAddMoreButton = [
      'field_volunteer_work' => 'paragraph_volunteer_work',
      'field_educational_training' => 'educational_formation',
      'field_professional_trajectory' => 'experience',
    ];
    $trajectoryFieldDescription = [
      'field_general_interest' => [
        'title' => $this->t('General interest'),
        'description' => $this->t('Tell us about you, your interest, your dreams, and goals.'),
      ],
      'field_educational_training' => [
        'title' => $this->t('Educational training'),
        'description' => $this->t('Inform your degree, including technical courses.'),
      ],
      'field_professional_trajectory' => [
        'title' => $this->t('Work experiences'),
        'description' => $this->t('Tell about your formal work experience.'),
      ],
      'field_volunteer_work' => [
        'title' => $this->t('Volunteer Work'),
        'description' => $this->t('Tell about your volunteer work experience.'),
      ],
    ];
    $trajectoryBody = &$form['field_user_professional_traject']['widget'][0];
    foreach ($trajectoryFieldDescription as $title => $field_info) {
      $title_text = $field_info['title'];
      $description_text = $field_info['description'];
      $fieldName = ($title . '_title');
      $this->defineGroupTitle($trajectoryBody['subform'], $fieldName, $title_text, $description_text, 'h4');
      $trajectoryBody['subform'][$fieldName]['#weight'] = $trajectoryBody['subform'][$title]['#weight'] - 0.01;
    }
    $trajectoryFields = &$trajectoryBody['subform'];
    foreach ($trajectoryFields as $trajectoryFieldName => &$trajectoryField) {
      if (array_key_exists($trajectoryFieldName, $trajectoryFieldNames)) {
        if (isset($trajectoryField['widget'])) {
          foreach ($trajectoryField['widget'] as $trajectorySubFieldPosition => &$trajectorySubField) {
            if (is_numeric($trajectorySubFieldPosition)) {
              $trajectorySubField['#title'] = '';
              foreach ($trajectoryFieldNames[$trajectoryFieldName] as $trajectorySubFormItemField => $trajectorySubFormItem) {
                $trajectorySubField['subform'][$trajectorySubFormItemField]['#prefix'] = $trajectorySubFormItem['prefix'];
                $trajectorySubField['subform'][$trajectorySubFormItemField]['#suffix'] = $trajectorySubFormItem['suffix'];
              }
            }
          }
        }
      }
    }
  }

}
