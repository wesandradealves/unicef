<?php

namespace Drupal\company_manager\Form;

use Drupal\address\Repository\SubdivisionRepository;
use Drupal\company\Service\CityFieldsService;
use Drupal\company\Service\CityStampService;
use Drupal\company_manager\Service\TalentAcquisitionFields;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\umio_helpers\Service\FormValidator;
use Drupal\user\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a Company Manager form.
 */
class RegisterCityManagerAdditionalForm extends FormBase {


  use DependencySerializationTrait;

  /**
   * Array with the fields per page.
   *
   * @var array
   */
  private $pageFields = [
    '1' => [
      'field_telephone',
      'field_telephone_extension',
      'field_phone',
      'field_address',
    ],
    '2' => [
      'field_company_unicef_stamp',
      'field_company_region',
      'field_company_role',
      'field_company_address',
      'field_institution_type',
      'field_opening',
    ],
    '3' => [
      'field_company_logo',
      'field_company_cover',
    ],
    'defaults' => [
      '#attributes',
      'page_info',
      'actions',
    ],
  ];

  /**
   * Define the subdivisionRepository.
   *
   * @var \Drupal\address\Repository\SubdivisionRepository
   */
  private $subdivisionRepository;

  /**
   * Helper class to validate input.
   *
   * @var \Drupal\umio_helpers\Service\FormValidator
   */
  private $formValidator;

  /**
   * Service to get talent acquisition user fields.
   *
   * @var \Drupal\company_manager\Service\TalentAcquisitionFields
   */
  private $talentAcquisitionFieldsService;

  /**
   * Define the cityFields.
   *
   * @var \Drupal\company\Service\CityFieldsService
   */
  protected $cityFields;

  /**
   * Define the cityStampService.
   *
   * @var \Drupal\company\Service\CityStampService
   */
  protected $cityStampService;

  /**
   * Constructor of the class.
   *
   * @param \Drupal\address\Repository\SubdivisionRepository $subdivisionRepository
   *   SubdivisionRepository service.
   * @param \Drupal\umio_helpers\Service\FormValidator $formValidator
   *   Helper class to validate input.
   * @param \Drupal\company_manager\Service\TalentAcquisitionFields $talentAcquisitionFields
   *   Service to get talent acquisition user fields.
   * @param \Drupal\company\Service\CityFieldsService $cityFields
   *   Service to get company fields.
   * @param \Drupal\company\Service\CityStampService $cityStampService
   *   Service to get company fields.
   */
  final public function __construct(
    SubdivisionRepository $subdivisionRepository,
    FormValidator $formValidator,
    TalentAcquisitionFields $talentAcquisitionFields,
    CityFieldsService $cityFields,
    CityStampService $cityStampService
  ) {
    $this->subdivisionRepository = $subdivisionRepository;
    $this->formValidator = $formValidator;
    $this->talentAcquisitionFieldsService = $talentAcquisitionFields;
    $this->cityFields = $cityFields;
    $this->cityStampService = $cityStampService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new static(
      $container->get('address.subdivision_repository'),
      $container->get('umio_helpers.form_validator'),
      $container->get('company_manager.talent_acquisition_fields'),
      $container->get('company.city_fields_service'),
      $container->get('company.city_stamp_service'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'register_city_manager_additional_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $current_page = $form_state->get('page');

    if ($current_page < count($this->pageFields)) {
      $this->setFormDefaultFields($form, $form_state);
    }

    $form['actions']['next'] = [
      '#type' => 'submit',
      '#button_type' => 'primary',
      '#value' => $this->t('Continue'),
      '#submit' => ['::pageNext'],
      '#weight' => 2,
      '#attributes' => [
        'class' => ['btn-next', 'btn', 'btn-primary'],
      ],
    ];

    if ($form_state->has('page') && $current_page >= 2) {
      $form['actions']['back'] = [
        '#type' => 'submit',
        '#value' => $this->t('Back'),
        '#submit' => ['::pageBack'],
        '#limit_validation_errors' => [],
        '#weight' => 1,
        '#attributes' => [
          'class' => ['btn-previous', 'btn'],
        ],
      ];
      // If page 3 and is unicef stamp will submit form.
      if ($current_page == (count($this->pageFields) - 1)) {
        $form['actions']['next']['#submit'] = ['::submitForm'];
      }
      elseif (($form_state->getValue('field_company_unicef_stamp') == 1 && $current_page == 3)) {
        unset($this->pageFields['4']);
        $form['actions']['next']['#submit'] = ['::submitForm'];
      }
      else {
        $form['actions']['next']['#submit'] = ['::pageNext'];
      }
    }

    $form['#attached']['library'] = [
      'company_manager/company_manager.city_manager_register',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    $telephone = $form_state->getValue('field_telephone');
    if ($telephone && !$this->formValidator->validateTelephone($telephone)) {
      $form_state->setErrorByName('field_telephone', $this->t('This field must be a valid telephone.'));
    }
    $currentPage = $form_state->get('page');
    if ($currentPage && $currentPage == 1) {
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

    $isStamp = $form_state->getValue('field_company_unicef_stamp', FALSE);
    $institutionType = $isStamp ? 'city' : $form_state->getValue('field_institution_type');
    if ($currentPage && $currentPage == 2) {
      // Region is only displayed for stamp cities.
      if ($isStamp && $form_state->getValue('field_company_region') == '') {
        $form_state->setErrorByName('field_company_region', $this->t('This field is required.'));
      }
      if ($institutionType == 'city' && $form_state->getValue('companyLocality') == '') {
        $form_state->setErrorByName('companyLocality', $this->t('This field is required.'));
      }
      // State is always required.
      if ($form_state->getValue('companyAdministrativeArea') == '') {
        $form_state->setErrorByName('companyAdministrativeArea', $this->t('This field is required.'));
      }
    }
    // If it's not stamp, the commitment term will be available for upload.
    if (!$isStamp) {
      $this->pageFields['4'] = [
        'field_company_commitment_term',
        'field_download_commitment_term_infos',
        'field_download_commitment_term',
        'field_company_commitment_term_infos',
      ];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $page_values = $this->saveAllCurrentInformation($form_state);
    $form_state
      ->setValues($page_values)
      ->set('page_values', $page_values);
    $currentUser = User::load($this->currentUser()->id());
    if ($currentUser) {
      $data = $form_state->getValues();

      $data['administrativeArea'] = $data['administrativeArea'] ? $data['administrativeArea'] : '';
      $data['locality'] = $data['locality'] ? $data['locality'] : '';

      if (!empty($data)) {
        $currentUser->set('field_user_telephone', $data['field_telephone']);
        $currentUser->set('field_user_telephone_extension', $data['field_telephone_extension']);
        $currentUser->set('field_user_phone', $data['field_phone']);

        $isStamp = $data['field_company_unicef_stamp'] == 1;

        $paragraph = Paragraph::create([
          'type' => 'paragraph_address',
          'field_paragraph_address' => [
            'langcode' => '',
            'country_code' => 'BR',
            'address_line1' => '',
            'dependent_locality' => $data['dependentLocality'],
            'administrative_area' => $data['administrativeArea'],
            'locality' => $data['locality'],
            'postal_code' => '',
          ],
        ]);
        $paragraph->save();
        $paragraphCreated = [
          [
            'target_id' => $paragraph->id(),
            'target_revision_id' => $paragraph->getRevisionId(),
          ],
        ];
        $currentUser->set('field_user_address', $paragraphCreated);

        $userStatus = $isStamp ? 'Ativo' : 'Aguardando Ativação';
        $currentUser->set('field_user_status', $userStatus);

        // This flag informs that users completed their registration.
        $currentUser->set('field_flag_singup', TRUE);
        $currentUser->save();

        // This is where register public section.
        $company_data = ['type' => 'company'];
        $company_data['field_company_email'] = $currentUser->get('mail')->getString();
        $company_data['field_company_logo'] = $data['field_company_logo'];
        $company_data['field_company_cover'] = $data['field_company_cover'];

        $company_data['field_company_unicef_stamp'] = $data['field_company_unicef_stamp'];

        if ($isStamp) {
          $company_data['field_company_type'] = 'city';
          $company_data['field_company_region'] = $data['field_company_region'];
        }
        else {
          $commitmentTerm = $company_data['field_company_commitment_term'] = $data['field_company_commitment_term'];
          $company_data['field_company_type'] = $data['field_institution_type'];
        }

        if ($company_data['field_company_type'] === 'city') {
          $company_data['field_company_corporate_name'] = $data['companyLocality'];
          $company_data['title'] = $data['companyLocality'];
        }
        else {
          $data['companyLocality'] = '';
          $states = $this->subdivisionRepository->getList(['BR']);
          $company_data['field_company_corporate_name'] = $states[$data['companyAdministrativeArea']];
          $company_data['title'] = $states[$data['companyAdministrativeArea']];
        }
        $company_data['field_company_telephone'] = $data['field_telephone'];
        $company_data['field_company_phone_extension'] = $data['field_telephone_extension'];
        $company_data['field_company_phone'] = $data['field_phone'];

        $company = Node::create($company_data);

        if (!$isStamp && isset($commitmentTerm) && !empty($commitmentTerm)) {
          $company->set('moderation_state', 'pending_approval');
        }
        elseif ($isStamp) {
          $company->set('moderation_state', 'pending_approval');
        }

        if (!$company->save()) {
          $this->messenger()->addError($this->t("An error occurred trying save the city."));
        }
        else {
          $currentUser->set('field_user_company', $company->id());
          $currentUser->set('field_user_company_role', $data['field_company_role']);
          $currentUser->save();
          $nodeCompany = Node::load($company->id());
          $paragraph = Paragraph::create([
            'type' => 'paragraph_address',
            'field_paragraph_address' => [
              'langcode' => '',
              'country_code' => 'BR',
              'administrative_area' => $data['companyAdministrativeArea'],
              'locality' => $data['companyLocality'],
              'dependent_locality' => '',
              'postal_code' => '',
              'address_line1' => '',
            ],
          ]);
          if ($paragraph->save()) {
            $this->cityStampService->updateStampTermDate($data['companyAdministrativeArea'], $data['companyLocality']);
          }
          $nodeCompany->field_company_address = [
            [
              'target_id' => $paragraph->id(),
              'target_revision_id' => $paragraph->getRevisionId(),
            ],
          ];
          $nodeCompany->save();
          $companyId = $nodeCompany->id();
          $url = Url::fromRoute('company_manager.success_register_city_manager', ['node' => $companyId]);
          $form_state->setRedirectUrl($url);
        }
      }
    }
  }

  /**
   * Set the definitions and the values for the fields.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  private function setFormDefaultFields(array &$form, FormStateInterface $form_state): void {
    if (!$form_state->has('page')) {
      $form_state->set('page', 1);
    }

    $page = $form_state->get('page');
    $form['page_info'] = [
      '#type' => 'markup',
      '#parents' => [],
      '#markup' => $this->getPageMarkup($page),
    ];

    $form_state->setValue('field_opening', 'new');

    $form = $this->talentAcquisitionFieldsService->createTalentAcquisitionFields($form, $form_state);
    $form['field_telephone']['#title'] = $this->t('Institutional phone');
    $form['field_company_role']['#title'] = $this->t('Role in your institution');
    $form['field_company_role']['#prefix'] = '';
    $form['field_company_role']['#suffix'] = '';
    $form['field_company_role']['#states'] = [
      'visible' => [
        'select[name="field_company_unicef_stamp"]' => ['!value' => ''],
      ],
    ];

    $form['field_telephone']['#prefix'] = '<div class="col-12"><div class="row"><div class="form-group col-8">';
    $form['field_telephone']['#suffix'] = '</div>';
    $form['field_telephone_extension']['#prefix'] = '<div class="form-group col-4">';
    $form['field_telephone_extension']['#suffix'] = '</div></div></div>';

    // Remove some fields fields.
    unset($form['field_name']);
    unset($form['field_company_role_started']);
    unset($form['field_email']);

    $form['field_address']['dependentLocality'] = [
      '#type' => 'textfield',
      '#title' => $this->t('District'),
      '#maxlength' => 50,
      '#default_value' => $form_state->getValue('dependentLocality', ''),
    ];

    $form = $this->cityFields->createCityFields($form, $form_state);

    $companyConfig = $this->config('company.settings');
    $form['field_download_commitment_term_infos']['#markup'] = $companyConfig->get('commitment_term_markup');

    $form['page_current_step'] = [
      '#type' => 'hidden',
      '#title' => $this->t('page_current_step'),
      '#value' => $page + 1,
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];

    // Defining order.
    $form['field_telephone']['#weight'] = 0;
    $form['field_telephone_extension']['#weight'] = 0;
    $form['field_phone']['#weight'] = 1;
    $form['field_address']['#weight'] = 2;
    $form['field_address']['locality']['#weight'] = 3;
    $form['field_address']['administrativeArea']['#weight'] = 3;
    $form['field_company_unicef_stamp']['#weight'] = 4;
    $form['field_institution_type']['#weight'] = 5;
    $form['field_company_region']['#weight'] = 6;
    $form['field_company_address']['#weight'] = 6;
    $form['field_company_role']['#weight'] = 7;
    $form['field_company_logo']['#weight'] = 8;
    $form['field_company_cover']['#weight'] = 9;
    $form['field_download_commitment_term_infos']['#weight'] = 10;
    $form['field_company_commitment_term']['#weight'] = 11;

    $this->defineFieldsExhibition($form, $form_state);
  }

  /**
   * Markup to print in the page of the form.
   *
   * @param int $page
   *   The current page of the form.
   *
   * @return string
   *   Markup to the current page.
   */
  private function getPageMarkup(int $page): string {
    $data = [
      1 => [
        '@title' => $this->t('Public sector'),
        '@description' => $this->t('Enter your contact details:'),
        '@institutionIcon' => 'ph-bank',
        '@icon' => 'ph-user',
        '@text' => $this->t('Contact details'),
      ],
      2 => [
        '@title' => $this->t('Public sector'),
        '@description' => $this->t("Inform the company data that you represent"),
        '@icon' => 'ph-hard-drives',
        '@text' => $this->t('Public sector details'),
        '@institutionIcon' => 'ph-bank',
      ],
      3 => [
        '@title' => $this->t('Public sector'),
        '@description' => $this->t("Now choose an image that represents your isntitution"),
        '@icon' => 'ph-buildings',
        '@text' => $this->t('Images'),
        '@institutionIcon' => 'ph-bank',
      ],
      4 => [
        '@title' => $this->t('Public sector'),
        '@description' => $this->t("Download the commitment to sign and finish the signup"),
        '@icon' => 'ph-buildings',
        '@text' => $this->t('Commitment term'),
        '@institutionIcon' => 'ph-bank',
      ],
    ];

    $markup = '';
    $markup = '
    <div class="form-page-title">
      <span class="page-title-title d-flex align-items-center"><i class="@institutionIcon mr-2"></i> @title</span>
      <span class="page-title-description">@description</span>
    </div>
    <div class="form-page-info">
      <span class="page-info-icon">
        <i class="@icon svg-white"></i>
      </span>
      <span class="page-info-text">@text</span>
    </div>';

    return str_replace(
      ['@title', '@description', '@icon', '@text', '@institutionIcon'],
      [
        $data[$page]['@title'],
        $data[$page]['@description'],
        $data[$page]['@icon'],
        $data[$page]['@text'],
        $data[$page]['@institutionIcon'],
      ],
      $markup
    );
  }

  /**
   * Define which fields are gonna be displayed in the form.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  private function defineFieldsExhibition(array &$form, FormStateInterface $form_state): void {
    $page = $form_state->get('page');
    $pageFields = $this->pageFields;
    foreach ($form as $fieldName => $field) {
      if (!in_array($fieldName, $pageFields[$page]) && !in_array($fieldName, $pageFields['defaults'])) {
        unset($form[$fieldName]['#required']);
        $fieldType = $field['#type'] ?? '';
        if ($fieldType == 'managed_file') {
          unset($form[$fieldName]['#title']);
          unset($form[$fieldName]['#description']);
          $form[$fieldName]['#field_prefix'] = '<div class="d-none">';
          $form[$fieldName]['#field_suffix'] = '</div>';
        }
        elseif ($fieldType == 'fieldset') {
          $form[$fieldName]['address']['#prefix'] = '<div class="d-none">';
          $form[$fieldName]['address']['#suffix'] = '</div>';
        }
        else {
          $form[$fieldName]['#prefix'] = '<div class="d-none">';
          $form[$fieldName]['#suffix'] = '</div>';
        }
      }
    }
  }

  /**
   * Submit function for the next page of the form.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function pageNext(array &$form, FormStateInterface $form_state): void {
    $page_values = $this->saveAllCurrentInformation($form_state);
    $page_next = $form_state->get('page') + 1;
    $form_state
      ->setValues($page_values)
      ->set('page_values', $page_values)
      ->set('page', $page_next)
      ->setRebuild();
  }

  /**
   * Function to back one page at the form.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function pageBack(array &$form, FormStateInterface $form_state): void {
    $page_back = $form_state->get('page') - 1;
    $form_state
      ->set('page', $page_back)
      ->setRebuild();
  }

  /**
   * Function to save all data submitted by the form.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  private function saveAllCurrentInformation(FormStateInterface &$form_state): array {
    $isStamp = $form_state->getValue('field_company_unicef_stamp', '');

    $page_values = $form_state->has('page_values') ? $form_state->get('page_values') : [];

    $page_values['field_company_unicef_stamp'] = $isStamp;
    $page_values['field_company_role'] = $form_state->getValue('field_company_role', '');

    $page_values['field_company_logo'] = $form_state->getValue('field_company_logo', 0);
    $page_values['field_company_cover'] = $form_state->getValue('field_company_cover', 0);
    $page_values['field_company_commitment_term'] = $form_state->getValue('field_company_commitment_term', 0);

    $page_values['field_telephone'] = $form_state->getValue('field_telephone', '');
    $page_values['field_telephone_extension'] = $form_state->getValue('field_telephone_extension', '');
    $page_values['field_phone'] = $form_state->getValue('field_phone', '');

    $page_values['field_address'] = $form_state->getValue('field_address', '');
    $page_values['dependentLocality'] = $form_state->getValue('dependentLocality', '');
    $page_values['administrativeArea'] = $form_state->getValue('administrativeArea', '');
    $page_values['locality'] = $form_state->getValue('locality', '');

    $page_values['field_company_email'] = $form_state->getValue('field_company_email', '');
    $page_values['field_company_region'] = $form_state->getValue('field_company_region', '');

    if ($form_state->get('page') == 2) {
      $institutionType = $isStamp
        ? 'city'
        : $form_state->getValue('field_institution_type', '');

      $page_values['field_institution_type'] = $institutionType;
      $companyAdministrativeArea = $form_state->getValue('companyAdministrativeArea', '');

      if ($isStamp) {
        // Load taxonomy in order to retrieve state acronym.
        $admAreaTerm = $this->cityStampService->getTermById($companyAdministrativeArea);
        $page_values['companyAdministrativeArea'] = $admAreaTerm->get('field_stamp_unicef_acronym')->getString();

        // Load taxonomy term in order to retrieve city name.
        $stampLocality = $form_state->getValue('companyLocality', '');
        $localityTerm = $this->cityStampService->getTermById($stampLocality);
        $page_values['companyLocality'] = $localityTerm->getName();
      }
      else {
        $page_values['companyAdministrativeArea'] = $companyAdministrativeArea;
        $page_values['companyLocality'] = ($institutionType === 'city')
          ? $form_state->getValue('companyLocality', '')
          : '';
      }
    }

    return $page_values;
  }

}
