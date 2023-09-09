<?php

namespace Drupal\company_manager\Service;

use Drupal\address\Repository\SubdivisionRepository;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\umio_helpers\Service\FormValidator;
use Drupal\user\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Implements the construction fields of the Talent Acquisition in form.
 */
class TalentAcquisitionFields {

  use DependencySerializationTrait;
  use StringTranslationTrait;


  /**
   * Define the entityTypeManager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * Define the FormValidator.
   *
   * @var \Drupal\umio_helpers\Service\FormValidator
   */
  protected $formValidator;

  /**
   * Define the subdivisionRepository.
   *
   * @var \Drupal\address\Repository\SubdivisionRepository
   */
  protected $subdivisionRepository;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Entity\EntityInterface
   */
  private $formUser;

  /**
   * Define the constructor.
   */
  final public function __construct(
    EntityTypeManager $entityTypeManager,
    FormValidator $formValidator,
    SubdivisionRepository $subdivisionRepository
  ) {
    $this->entityTypeManager = $entityTypeManager;
    $this->formValidator = $formValidator;
    $this->subdivisionRepository = $subdivisionRepository;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('umio_helpers.form_validator'),
      $container->get('address.subdivision_repository'),
    );
  }

  /**
   * The construct method.
   *
   * @param int $uid
   *   The current user.
   */
  public function setFormUser(int $uid) : void {
    $this->formUser = $this->entityTypeManager->getStorage('user')->load($uid);
  }

  /**
   * Creating common fields in company/civil-society.
   *
   * @param array $form
   *   Define the form variable array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Define the $form_state variable.
   *
   * @return array
   *   The customized form array.
   */
  public function createTalentAcquisitionFields(array $form, FormStateInterface $form_state): array {
    $selectedArea = $form_state->getValue('administrativeArea', '');
    $form['field_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Name'),
      '#default_value' => $form_state->getValue('field_name', ''),
      '#required' => TRUE,
      '#maxlength' => 50,
    ];

    $form['field_email'] = [
      '#type' => 'email',
      '#title' => $this->t('Main Email'),
      '#default_value' => $form_state->getValue('field_email', ''),
      '#required' => TRUE,
      '#disabled' => $form_state->getValue('field_opening') != 'new' ? 'disabled' : '',
    ];

    $form['field_company_role'] = [
      '#type' => 'textfield',
      '#prefix' => '<div class="row"><div class="col-lg-7">',
      '#suffix' => '</div>',
      '#title' => $this->t('Role on company'),
      '#default_value' => $form_state->getValue('field_company_role', ''),
      '#maxlength' => 20,
      '#required' => TRUE,
    ];

    $form['field_company_role_started'] = [
      '#type' => 'date',
      '#prefix' => '<div class="col-lg-5">',
      '#suffix' => '</div></div>',
      '#title' => $this->t('Started at'),
      '#default_value' => $form_state->getValue('field_company_role_started', ''),
      '#maxlength' => 20,
      '#required' => TRUE,
      '#date_date_format' => 'm/Y',
      '#attributes' => [
        'type' => 'month',
      ],
    ];

    $form['field_telephone'] = [
      '#type' => 'tel',
      '#attributes' => [
        'class' => [
          'telephone-mask',
        ],
      ],
      '#title' => $this->t('Landline'),
      '#prefix' => '<div class="row"><div class="col-7">',
      '#suffix' => '</div>',
      '#default_value' => $form_state->getValue('field_telephone', ''),
      '#required' => TRUE,
    ];

    $form['field_telephone_extension'] = [
      '#type' => 'number',
      '#title' => $this->t('Phone extension'),
      '#prefix' => '<div class="col-5">',
      '#suffix' => '</div></div>',
      '#attributes' => [
        'class' => [
          'telephone-extension-mask',
        ],
      ],
      '#default_value' => $form_state->getValue('field_telephone_extension', ''),
    ];

    $form['field_phone'] = [
      '#type' => 'tel',
      '#attributes' => [
        'class' => [
          'phone-mask',
        ],
      ],
      '#title' => $this->t('Cell Phone'),
      '#default_value' => $form_state->getValue('field_phone', ''),
    ];

    $states = $this->subdivisionRepository->getList(['BR']);
    $states = array_merge(['' => $this->t('Select...')], $states);

    $form['field_address']['administrativeArea'] = [
      '#type' => 'select',
      '#title' => $this->t('Administrative area'),
      '#name' => 'administrativeArea',
      '#prefix' => '<div class="col-12"><div class="row"><div class="col-6">',
      '#suffix' => '</div>',
      '#default_value' => $form_state->getValue('administrativeArea', ''),
      '#options' => $states,
      '#ajax' => [
        'callback' => [$this, 'reloadLocalitySelect'],
        'event' => 'change',
      ],
      '#required' => TRUE,
    ];

    $form['field_address']['locality'] = [
      '#type' => 'textfield',
      '#title' => $this->t('City'),
      '#prefix' => '<div id="form-item-locality-pre" class="col-6">',
      '#suffix' => '</div></div></div>',
      '#options' => $this->subdivisionRepository->getList(['BR', $selectedArea]),
      '#validated' => TRUE,
      '#id' => "locality-select",
      '#name' => 'locality',
      '#attributes' => [
        'data-drupal-selector' => "edit-locality",
        'data-select2-id' => "edit-locality",
        'placeholder' => $this->t('First select state'),
        'class' => [
          'form-select ',
          'valid',
        ],
      ],
      '#default_value' => $form_state->getValue('locality', ''),
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * Creating common fields in company/civil-society.
   *
   * @param array $form
   *   Define the form variable array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Define the $form_state variable.
   *
   * @return array
   *   The customized form array.
   */
  public function createTalentAcquisitionFieldsDisplay(array $form, FormStateInterface $form_state): array {
    /** @var \Drupal\user\Entity\User $entity */
    $entity = $this->formUser;
    $form_state->set('entity', $entity);

    $this->verifyUserParagraphs($entity);

    /** @var \Drupal\Core\Entity\Display\EntityFormDisplayInterface $form_display */
    $form_display = $this->entityTypeManager->getStorage('entity_form_display')->load('user.user.talent_acquisition_profile');
    $form_state->set('form_display', $form_display);
    $form['#parents'] = [];

    // Get the Image Field.
    $form = $this->getImagesFields($form, $form_state);

    // Get the Basic Fields.
    $form = $this->getBasicFields($form, $form_state);

    // Get the component fields.
    foreach ($form_display->getComponents() as $name => $component) {

      /** @var \Drupal\paragraphs\Plugin\Field\FieldWidget\InlineParagraphsWidget $widget */
      $widget = $form_display->getRenderer($name);
      $items = $entity->get($name);
      $items->filterEmptyItems();
      $form[$name] = $widget->form($items, $form, $form_state);
      $form[$name]['#access'] = $items->access('edit');
    }

    return $form;

  }

  /**
   * Creating the logo and cover fields.
   *
   * @param array $form
   *   Define the form variable array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Define the $form_state variable.
   *
   * @return array
   *   The customized form array.
   */
  private function getBasicFields(array $form, FormStateInterface $form_state): array {

    /** @var \Drupal\user\Entity\User $talentAcquisitionUser */
    $talentAcquisitionUser = $this->formUser;

    $talentAcquisitionUserMail = $talentAcquisitionUser->get('mail')->getValue()[0]['value'];
    $form['field_user_email'] = [
      '#type'                 => 'textfield',
      '#title'                => $this->t('Mail'),
      '#default_value'        => $talentAcquisitionUserMail,
      '#attributes' => [
        'disabled' => 'disabled',
        'class' => [],
      ],
    ];

    $talentAcquisitionPhoneExtension = $talentAcquisitionUser->get('field_user_telephone_extension')->getValue();
    if (!empty($talentAcquisitionPhoneExtension)) {
      $talentAcquisitionPhoneExtension = $talentAcquisitionPhoneExtension[0]['value'];
    }
    $form['field_user_telephone_extension'] = [
      '#type' => 'number',
      '#title' => $this->t('Phone extension'),
      '#attributes' => [
        'class' => [
          'telephone-extension-mask',
        ],
      ],
      '#default_value' => $talentAcquisitionPhoneExtension,
      '#maxlength' => 20,
    ];

    return $form;
  }

  /**
   * Check if the user has the Curriculum and Social paragraph.
   *
   * @param \Drupal\user\Entity\User $talentAcquisitionUser
   *   Define the user that will pass by the paragraphs checks.
   */
  public function verifyUserParagraphs(User &$talentAcquisitionUser): void {
    $paragraphBones = [
      'field_user_professional_traject' => [
        'type' => 'paragraph_trajectory',
        'field_educational_training' => [],
        'field_general_interest' => [],
        'field_volunteer_work' => [],
        'field_professional_trajectory' => [],
      ],
      'field_user_social' => [
        'type' => 'paragraph_social',
        'field_paragraph_networks' => [],
      ],
      'field_user_address' => [
        'type' => 'paragraph_address',
        'field_paragraph_address' => [
          'langcode' => '',
          'country_code' => 'BR',
          'address_line1' => '',
          'dependent_locality' => '',
          'administrative_area' => '',
          'locality' => '',
          'postal_code' => '',
        ],
      ],
    ];

    foreach ($paragraphBones as $userParagraphField => $paragraphBone) {
      if ($talentAcquisitionUser->get($userParagraphField)->getValue() === []) {
        $paragraph = Paragraph::create($paragraphBone);
        $paragraph->save();
        $paragraphCreated = [
          [
            'target_id' => $paragraph->id(),
            'target_revision_id' => $paragraph->getRevisionId(),
          ],
        ];
        $talentAcquisitionUser->set($userParagraphField, $paragraphCreated);
      }
    }

    $talentAcquisitionUser->save();
  }

  /**
   * Creating the logo and cover fields.
   *
   * @param array $form
   *   Define the form variable array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Define the $form_state variable.
   *
   * @return array
   *   The customized form array.
   */
  private function getImagesFields(array $form, FormStateInterface $form_state): array {

    /** @var \Drupal\user\Entity\User $talentAcquisitionUser */
    $talentAcquisitionUser = $this->formUser;
    $talentAcquisitionUserPhoto = $talentAcquisitionUser->get('field_user_profile_picture')->getValue();

    $talentAcquisitionUserPhoto = !empty($talentAcquisitionUserPhoto[0]['target_id']) ? ['target_id' => $talentAcquisitionUserPhoto[0]['target_id']] : [];

    $form['field_user_profile_picture'] = [
      '#type'                 => 'managed_file',
      '#upload_location'      => 'public://user/profile',
      '#upload_validators'    => [
        'file_validate_is_image'      => [],
        'file_validate_extensions'    => ['gif png jpg jpeg'],
        'file_validate_size'          => [2 ** 20 * 5],
      ],
      '#title'                => $this->t('Profile picture'),
      '#description'          => $this->t('Allowed extensions: @extensions', ['@extensions' => 'gif png jpg jpeg']),
      '#default_value'        => $talentAcquisitionUserPhoto,
      '#attributes' => [
        'class' => [
          $form_state->getValue('field_opening') != 'new' ? 'image-preview' : '',
        ],
      ],
    ];

    return $form;
  }

  /**
   * Validate the fields for the talent acquisition.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Define the $form_state variable.
   */
  public function validateFields(FormStateInterface $form_state): void {
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
  }

  /**
   * Callback ajax.
   *
   * Selects and returns the cities of the state.
   *
   * @param array $form
   *   Define the form variable array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Define the $form_state variable.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   A AjaxResponse calling .
   */
  public function reloadLocalitySelect(array &$form, FormStateInterface $form_state) : AjaxResponse {
    $selected_area = $form_state->getUserInput()['administrativeArea'];

    $cities = $this->subdivisionRepository->getList(['BR', $selected_area]);
    $cities = array_merge(['' => $this->t('Select...')], $cities);
    $prefix = $form['field_address']['locality']['#prefix'];
    $suffix = $form['field_address']['locality']['#suffix'];
    $form['field_address']['locality'] = [
      '#type' => 'select',
      '#name' => "locality",
      '#title' => $this->t('City'),
      '#prefix' => $prefix,
      '#suffix' => $suffix,
      '#id' => "locality-select",
      '#attributes' => [
        'data-drupal-selector' => "edit-locality",
        'data-select2-id' => "edit-locality",
        'class' => [
          'form-select ',
          'valid',
        ],
      ],
      '#default_value' => '',
      '#disabled' => 'disabled',
      'aria-hidden' => "true",
      '#required' => TRUE,
    ];

    if (isset($selected_area) && !empty($selected_area)) {

      $form['field_address']['locality']['#disabled'] = '';
      $form['field_address']['locality']['#options'] = $cities;

    }

    $response = new AjaxResponse();
    $response->addCommand(new ReplaceCommand("#form-item-locality-pre", ($form['field_address']['locality'])));
    $response->addCommand(new InvokeCommand('', 'select2ReRun', ["#locality-select"]));
    return $response;

  }

}
