<?php

namespace Drupal\umio_younger\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;
use Drupal\umio_helpers\Service\FormValidator;
use Drupal\umio_younger\Service\YoungFieldsService;
use Drupal\user\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form to register a young user.
 */
class EditYoungForm extends FormBase {

  /**
   * Define the youngFieldsService.
   *
   * @var \Drupal\umio_younger\Service\YoungFieldsService
   */
  protected $youngFieldsService;

  /**
   * Node ID of the User.
   *
   * @var int|null
   */
  protected $uid;

  /**
   * Define the FormValidator.
   *
   * @var \Drupal\umio_helpers\Service\FormValidator
   */
  protected $formValidator;

  /**
   * {@inheritdoc}
   */
  final public function __construct(FormValidator $formValidator, YoungFieldsService $youngFieldsService) {
    $this->formValidator = $formValidator;
    $this->youngFieldsService = $youngFieldsService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new static(
      $container->get('umio_helpers.form_validator'),
      $container->get('umio_younger.young_fields')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'edit_young_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getRole(): string {
    return 'young';
  }

  /**
   * Form builder.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param int $user
   *   The ID of the requested user.
   *
   * @return array
   *   The form structure
   */
  public function buildForm(array $form, FormStateInterface $form_state, int $user = NULL): array {
    // Set the uid of the requested user.
    $this->uid = $user;

    // Make form configurations.
    $this->youngFieldsService->setFormUser($this->uid);
    $form = $this->youngFieldsService->createYoungFormFields($form, $form_state);

    $this->defineGeneralCustomizations($form);
    $this->defineFieldsWeigths($form);
    $this->defineFormButtons($form);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {

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

    if ($user->save()) {
      \Drupal::messenger()->addMessage($this->t('Form Submitted Successfully'), 'status', TRUE);
    }
  }

  /**
   * Define the form buttons.
   *
   * @param array $form
   *   The $form array of the application.
   */
  public function defineFormButtons(array &$form): void {

    $form['actions']['save'] = [
      '#type' => 'submit',
      '#button_type' => 'primary',
      '#value' => $this->t('Save'),
      '#submit' => ['::submitForm'],
      '#validate' => ['::validateForm'],
      '#attributes' => [
        'class' => ['btn-next', 'btn', 'btn-primary'],
      ],
      '#prefix' => '<div class="col-12 d-flex justify-content-center align-items-center button-form">',
      '#suffix' => '</div>',
    ];

    $form['actions']['preview'] = [
      '#type' => 'link',
      '#title' => $this->t('Preview Profile'),
      '#route_name' => 'entity.user.canonical',
      '#url' => Url::fromRoute('entity.user.canonical', ['user' => $this->uid]),
      '#limit_validation_errors' => [],
      '#attributes' => [
        'class' => ['btn-previous', 'btn'],
      ],
      '#prefix' => '<div class="col-12 d-flex justify-content-center align-items-center button-form">',
      '#suffix' => '</div>',
    ];
  }

  /**
   * Make some changes on the form fields to adjust the position of the fields.
   *
   * @param array $form
   *   The $form array of the application.
   */
  public function defineFieldsWeigths(array &$form): void {
    // Define the form field weight.
    $weight = 0;

    $form['field_user_profile_picture']['#weight'] = ++$weight;

    $form['field_group_title_basic_data']['#weight'] = ++$weight;
    $form['field_user_name']['#weight'] = ++$weight;
    $form['field_user_email']['#weight'] = ++$weight;
    $form['field_user_cpf']['#weight'] = ++$weight;
    $form['field_user_birth_date']['#weight'] = ++$weight;
    $form['field_user_gender']['#weight'] = ++$weight;
    $form['field_user_phone']['#weight'] = ++$weight;
    $form['field_user_address']['#weight'] = ++$weight;
    $form['field_group_description_note']['#weight'] = ++$weight;

    $form['field_group_title_curriculum']['#weight'] = ++$weight;
    $form['field_user_curriculum']['#weight'] = ++$weight;

    $form['field_group_title_social_network']['#weight'] = ++$weight;
    $form['field_user_social']['#weight'] = ++$weight;

    $form['actions']['#weight'] = ++$weight;
  }

  /**
   * Make some display changes on the form and call the fields customizations.
   *
   * @param array $form
   *   The $form array of the application.
   */
  public function defineGeneralCustomizations(array &$form): void {

    $form['#prefix'] = '<div class= "edit-young-form-region-content">';
    $form['#suffix'] = '</div>';

    $this->defineGroupTitle($form, 'field_group_title_basic_data', $this->t('Personal Data'));
    $this->defineGroupTitle($form, 'field_group_title_social_network', $this->t('Socials Networks'), $this->t('Enter your profile username'));
    $this->defineGroupTitle($form, 'field_group_title_curriculum', $this->t('Your Curriculum'));
    $this->defineGroupTitle($form, 'field_group_description_note', $this->t('If you need to change the blocked data, please contact us by email contato@1mio.com.br'), NULL, 'p');

    // Define the form customizations functions general and calls.
    $this->defineFieldsCustomizations($form);

  }

  /**
   * Make some display changes on the form fields.
   *
   * @param array $form
   *   The $form array of the application.
   */
  public function defineFieldsCustomizations(array &$form): void {

    $form['field_user_profile_picture']['#title_display'] = 'invisible';
    $form['field_user_profile_picture']['#description'] = '';
    $form['field_user_name']['widget'][0]['value']['#title'] = $this->t('How do you want to be called');
    $form['field_user_cpf']['widget'][0]['value']['#attributes']['disabled'] = ['disabled'];
    $form['field_user_cpf']['widget'][0]['value']['#attributes']['class'] = ['cpf-mask'];
    $form['field_user_birth_date']['widget'][0]['value']['#attributes']['disabled'] = ['disabled'];
    $form['field_user_phone']['widget'][0]['value']['#attributes']['class'] = ['phone-mask'];
    // Define the icons on the form external buttons.
    if (isset($form['field_user_curriculum']['widget']['add_more'])) {
      $form['field_user_curriculum']['widget']['add_more']['add_more_button_curriculum']['#prefix'] = '<i class="ph-plus edit--young--add-more-button align-items-center"></i>';
    }
    if (isset($form['field_user_social']['widget']['add_more'])) {
      $form['field_user_social']['widget']['add_more']['add_more_button_paragraph_social']['#prefix'] = '<i class="ph-plus edit--young--add-more-button align-items-center"></i>';
    }

    $paragraphRemoveInfos = [
      'field_user_address',
      'field_user_curriculum',
      'field_user_social',
    ];

    foreach ($paragraphRemoveInfos as $fieldToRemoveParagraphInfo) {
      if (isset($form[$fieldToRemoveParagraphInfo]['widget'][0]['info'])) {
        $form[$fieldToRemoveParagraphInfo]['widget'][0]['info'] = [];
      }
    }

    // Define the curriculum customizations.
    if (isset($form['field_user_curriculum']['widget'][0]['subform'])) {
      $this->defineCurriculumCustomizations($form);
    }
    // Define the socials networks customization.
    if (isset($form['field_user_social']['widget'][0]['subform'])) {
      $this->defineSocialCustomizations($form);
    }

  }

  /**
   * Make some display changes on the form fields.
   *
   * @param array $form
   *   The $form array of the application.
   */
  public function defineSocialCustomizations(array &$form): void {
    $socialsOrder = [
      'instagram' => 0,
      'facebook' => 1,
      'linkedin' => 2,
      'twitter' => 3,
      'tiktok' => 4,
    ];
    $socialUrl = [
      'instagram'   => 'https://instagram.com/',
      'facebook'  => 'https://facebook.com/',
      'linkedin'  => 'https://linkedin.com/',
      'twitter'   => 'https://twitter.com/',
      'tiktok'  => 'https://tiktok.com/',
    ];
    $userSocials = &$form['field_user_social']['widget'][0]['subform']['field_paragraph_networks']['widget'][0]['platform_values'];
    $prefixWithSocialIcon = '<div class="d-flex justify-content-center align-items-center form-item-@social"><i class="umio-edit-profile-social-icon @logo"></i>';
    foreach ($userSocials as $userSocial => &$socialData) {
      $socialData['value']['#title'] = '';
      $socialData['value']['#description'] = $socialUrl[$userSocial];
      $socialData['value']['#field_prefix'] = str_replace(
        ['@social', '@logo'],
        [$userSocial, 'ph-' . $userSocial . '-logo'],
        $prefixWithSocialIcon
      );
      $socialData['value']['#field_suffix'] = '</div>';
      $socialData['value']['#attributes']['placeholder'] = $this->t('Insert here your @social user', ['@social' => ucfirst($userSocial)]);
      $socialData['value']['#weight'] = $socialsOrder[$userSocial];
    }
  }

  /**
   * Make some display changes on the form fields.
   *
   * @param array $form
   *   The $form array of the application.
   */
  public function defineCurriculumCustomizations(array &$form): void {
    $curriculumConfigWithPrefix = [
      'prefix' => '<div class="d-md-flex justify-content-between align-items-center">',
      'suffix' => '',
    ];
    $curriculumConfigWithSuffix = [
      'prefix' => '',
      'suffix' => '</div>',
    ];
    $curriculumFieldNames = [
      'field_paragraph_courses' => [
        'field_course_begin' => $curriculumConfigWithPrefix,
        'field_course_current' => $curriculumConfigWithSuffix,
      ],
      'field_paragraph_education' => [
        'field_education_begin' => $curriculumConfigWithPrefix,
        'field_education_current' => $curriculumConfigWithSuffix,
      ],
      'field_paragraph_experience' => [
        'field_experience_begin' => $curriculumConfigWithPrefix,
        'field_experience_current' => $curriculumConfigWithSuffix,
      ],
    ];
    $curriculumFieldAddMoreButton = [
      'field_paragraph_courses' => 'courses_and_abilities',
      'field_paragraph_education' => 'educational_formation',
      'field_paragraph_experience' => 'experience',
    ];
    $curriculumFieldDescription = [
      'field_about_you' => [
        'title' => $this->t('About you'),
        'description' => $this->t('Tell us about you, your dreams, and goals.'),
      ],
      'field_what_do_you_search' => [
        'title' => $this->t('What are you looking professionally?'),
        'description' => $this->t('Talk about the areas you want to work on.'),
      ],
      'field_paragraph_courses' => [
        'title' => $this->t('Courses and skills'),
        'description' => $this->t('In addition to courses with a certificate, inform other forms of learning that contribute to the work. Ex: workshops, hackathons, online courses, and even what you learned independently.'),
      ],
      'field_paragraph_education' => [
        'title' => $this->t('Educational training'),
        'description' => $this->t('Inform your degree, including technical courses.'),
      ],
      'field_paragraph_experience' => [
        'title' => $this->t('Work experiences'),
        'description' => $this->t('Works with a formal contract, volunteer work, ventures, and work experiences in family businesses or in the community.'),
      ],
      'field_achievements' => [
        'title' => $this->t('Achievements'),
        'description' => $this->t('Would you like to share a moment that you consider an important achievement?.'),
      ],
    ];
    $curriculumBody = &$form['field_user_curriculum']['widget'][0];
    foreach ($curriculumFieldDescription as $title => $field_info) {
      $title_text = $field_info['title'];
      $description_text = $field_info['description'];
      $fieldName = ($title . '_title');
      $this->defineGroupTitle($curriculumBody['subform'], $fieldName, $title_text, $description_text, 'h4');
      $curriculumBody['subform'][$fieldName]['#weight'] = $curriculumBody['subform'][$title]['#weight'] - 0.01;
    }
    $curriculumFields = &$curriculumBody['subform'];
    foreach ($curriculumFields as $curriculumFieldName => &$curriculumField) {
      if (array_key_exists($curriculumFieldName, $curriculumFieldNames)) {
        if (isset($curriculumField['widget'])) {
          // Add the plus icon to the button add_more.
          $button_name = $curriculumFieldAddMoreButton[$curriculumFieldName];
          $curriculumField['widget']['add_more']['add_more_button_' . $button_name]['#prefix'] = '<i class="ph-plus edit--young--add-more-button align-items-center"></i>';
          foreach ($curriculumField['widget'] as $curriculumSubFieldPosition => &$curriculumSubField) {
            if (is_numeric($curriculumSubFieldPosition)) {
              $curriculumSubField['#title'] = '';
              foreach ($curriculumFieldNames[$curriculumFieldName] as $curriculumSubFormItemField => $curriculumSubFormItem) {
                $curriculumSubField['subform'][$curriculumSubFormItemField]['#prefix'] = $curriculumSubFormItem['prefix'];
                $curriculumSubField['subform'][$curriculumSubFormItemField]['#suffix'] = $curriculumSubFormItem['suffix'];
              }
            }
          }
        }
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
  public function saveBasicData(User &$user, array $data): void {

    $field_user_profile_picture = $data['field_user_profile_picture'] ? $data['field_user_profile_picture'] : NULL;
    $field_user_name = $data['field_user_name'] ? $data['field_user_name'] : NULL;
    $field_user_phone = $data['field_user_phone'] ? $data['field_user_phone'] : NULL;

    $user->set('field_user_profile_picture', $field_user_profile_picture);
    $user->set('field_user_name', $field_user_name);
    $user->set('field_user_phone', $field_user_phone);

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

}
