<?php

namespace Drupal\company_manager\Form;

use Drupal\company\Service\CompanyService;
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
 * Form to add additional data to partner user after confirmation of e-mail.
 */
class RegisterCompanyManagerAdditionalForm extends FormBase {

  use DependencySerializationTrait;

  /**
   * Array with the fields per page.
   *
   * @var array
   */
  private const PAGES_FIELDS = [
    '1' => [
      'field_telephone',
      'field_telephone_extension',
      'field_phone',
      'field_address',
    ],
    '2' => [
      'field_company',
      'field_company_role',
      'link_company_add_title',
      'link_company_register',
      'link_company_add_text',
      'link_company_branch_register',
    ],
    'defaults' => [
      '#attributes',
      'page_info',
      'actions',
    ],
  ];

  /**
   * Company service helper.
   *
   * @var \Drupal\company\Service\CompanyService
   */
  private $companyService;

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
   * Constructor of the class.
   *
   * @param \Drupal\company\Service\CompanyService $companyService
   *   Company service helper.
   * @param \Drupal\umio_helpers\Service\FormValidator $formValidator
   *   Helper class to validate input.
   * @param \Drupal\company_manager\Service\TalentAcquisitionFields $talentAcquisitionFields
   *   Service to get talent acquisition user fields.
   */
  final public function __construct(
    CompanyService $companyService,
    FormValidator $formValidator,
    TalentAcquisitionFields $talentAcquisitionFields
  ) {
    $this->companyService = $companyService;
    $this->formValidator = $formValidator;
    $this->talentAcquisitionFieldsService = $talentAcquisitionFields;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) : self {
    return new static(
      $container->get('company.company_service'),
      $container->get('umio_helpers.form_validator'),
      $container->get('company_manager.talent_acquisition_fields'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'register_company_manager_additional_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $current_page = $form_state->get('page');

    if ($current_page < 3) {
      $this->setFormDefaultFields($form, $form_state);
    }

    $form['actions']['next'] = [
      '#type' => 'submit',
      '#button_type' => 'primary',
      '#value' => $this->t('Continue'),
      '#submit' => ['::submitPageOne'],
      '#weight' => 2,
      '#attributes' => [
        'class' => ['btn-next', 'btn', 'btn-primary'],
      ],
    ];
    if ($form_state->has('page') && $current_page == 2) {
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
      $form['actions']['next']['#submit'] = ['::submitPageTwo', '::submitForm'];
    }

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

    $current_page = $form_state->get('page');
    if ($current_page && $current_page == 1) {
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

      $postalCode = $form_state->getValue('postalCode');
      if ($postalCode && !$this->formValidator->validatePostalCode($postalCode)) {
        $form_state->setErrorByName('postalCode', $this->t('This field must be a valid postal code.'));
      }
    }
    elseif ($current_page && $current_page == 2) {
      $company = $form_state->getValue('field_company');
      if ($company) {
        $node = Node::load($company);
        if (!$node) {
          $form_state->setErrorByName('field_company', $this->t('Please inform a valid and active company.'));
        }
      }
      else {
        $form_state->setErrorByName('field_company', $this->t('You must select the company you work for.'));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $currentUser = User::load($this->currentUser()->id());
    if ($currentUser) {
      $this->submitBaseForm($form, $form_state);
      $url = '';
      $company = $form_state->getValue('field_company');
      if ($company) {
        $url = Url::fromRoute('company_manager.success_register');

        // This flag informs that users completed their registration.
        $currentUser->set('field_flag_singup', TRUE);
        $currentUser->save();
      }
      else {
        $url = Url::fromRoute('company.register');
      }
      $form_state->setRedirectUrl($url);
    }
  }

  /**
   * Create a new Company entity type as a branch.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function submitFormCreatingNewBranch(array &$form, FormStateInterface $form_state): void {
    $currentUser = User::load($this->currentUser()->id());
    if ($currentUser) {
      $this->submitBaseForm($form, $form_state);
      $url = Url::fromRoute('company.branch_register');
      $form_state->setRedirectUrl($url);
    }
  }

  /**
   * Save the user base data.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function submitBaseForm(array &$form, FormStateInterface $form_state): void {
    $currentUser = User::load($this->currentUser()->id());
    if ($currentUser) {
      $data = $form_state->getValues();

      $data['administrativeArea'] = $data['administrativeArea'] ? $data['administrativeArea'] : '';
      $data['locality'] = $data['locality'] ? $data['locality'] : '';

      if (!empty($data)) {
        $currentUser->set('field_user_telephone', $data['field_telephone']);
        $currentUser->set('field_user_telephone_extension', $data['field_telephone_extension']);
        $currentUser->set('field_user_phone', $data['field_phone']);

        $paragraph = Paragraph::create([
          'type' => 'paragraph_address',
          'field_paragraph_address' => [
            'langcode' => '',
            'country_code' => 'BR',
            'address_line1' => '',
            'dependent_locality' => '',
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

        if ($data['field_company']) {
          $selectedCompany = Node::load($data['field_company']);
          $mainOfficeCompany = $this->companyService->getMainBranchFromCompany($selectedCompany);
          $currentUser->set('field_user_company', $mainOfficeCompany);
        }
        $currentUser->set('field_user_company_role', $data['field_company_role']);

        $currentUser->set('field_user_status', 'Aguardando Ativação');
        $currentUser->save();
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
  private function setFormDefaultFields(array &$form, FormStateInterface $form_state) : void {
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
    // Remove fields from account login.
    unset($form['field_name']);
    unset($form['field_email']);

    $form['field_company'] = [
      '#type' => 'select',
      '#title' => $this->t('Company'),
      '#options' => $this->getCompaniesOptions(),
      '#default_value' => $form_state->getValue('field_company', ''),
      '#attributes' => [
        'class' => [
          'select2-without-allow-clear select-list-institution',
        ],
      ],
    ];

    $form['link_company_add_title'] = [
      '#type' => 'markup',
      '#parents' => ['form'],
      '#markup' => '<b class="pt-3 text-muted">' . $this->t("Your company aren't registrated yet? you can:") . '</b>',
    ];

    $form['link_company_register'] = [
      '#type' => 'submit',
      '#value' => $this->t('Register headquarter'),
      '#name' => 'link_company_register',
      '#submit' => [
        '::submitPageTwo',
        '::submitForm',
      ],
      '#validate' => ['::validateEmptyFieldCompany'],
      '#attributes' => [
        'class' => [
          'link-class',
        ],
      ],
      '#prefix' => '<div class="pb-3 inline-flex-group content-institution-or-branch">',
    ];

    $form['link_company_add_text'] = [
      '#type' => 'markup',
      '#parents' => ['form'],
      '#markup' => '<b>' . $this->t('or') . '</b>',
    ];

    $form['link_company_branch_register'] = [
      '#type' => 'submit',
      '#value' => $this->t('Register branch'),
      '#name' => 'link_company_register',
      '#submit' => [
        '::submitPageTwo',
        '::submitFormCreatingNewBranch',
      ],
      '#attributes' => [
        'class' => [
          'link-class',
        ],
      ],
      '#suffix' => '</div>',
    ];

    $form['page_current_step'] = [
      '#type' => 'hidden',
      '#title' => $this->t('page_current_step'),
      '#value' => $page + 1,
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];

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
  private function getPageMarkup(int $page) : string {
    $data = [
      1 => [
        '@title' => $this->t('Personal data'),
        '@description' => $this->t('Enter your contact details:'),
        '@icon' => 'ph-user',
        '@text' => $this->t('Contact details'),
      ],
      2 => [
        '@title' => $this->t('Company'),
        '@description' => $this->t("Select your company. If you don't find it in the listing, you can register it:"),
        '@icon' => 'ph-hard-drives',
        '@text' => $this->t('Company'),
      ],
    ];

    $markup = '';
    $markup = '
    <div class="form-page-title">
      <span class="page-title-title d-flex align-items-center">@title</span>
      <span class="page-title-description">@description</span>
    </div>
    <div class="form-page-info">
      <span class="page-info-icon">
        <i class="@icon svg-white"></i>
      </span>
      <span class="page-info-text">@text</span>
    </div>';

    return str_replace(
      ['@title', '@description', '@icon', '@text'],
      [
        $data[$page]['@title'],
        $data[$page]['@description'],
        $data[$page]['@icon'],
        $data[$page]['@text'],
      ],
      $markup
    );
  }

  /**
   * Returns an array with the active companies to use in select field.
   *
   * @return array
   *   Array with active companies options.
   */
  private function getCompaniesOptions() : array {
    $companies = [];
    $companies[''] = $this->t("Select...");
    $companies = $companies + $this->companyService->getActiveCompanies();
    return $companies;
  }

  /**
   * Define which fields are gonna be displayed in the form.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  private function defineFieldsExhibition(array &$form, FormStateInterface $form_state) : void {
    $page = $form_state->get('page');
    foreach ($form as $field_name => $field) {
      if (!in_array($field_name, self::PAGES_FIELDS[$page]) && !in_array($field_name, self::PAGES_FIELDS['defaults'])) {
        unset($form[$field_name]['#required']);
        if (isset($field['#type']) && $field['#type'] == 'managed_file') {
          unset($form[$field_name]['#title']);
          unset($form[$field_name]['#description']);
          $form[$field_name]['#field_prefix'] = '<div class="d-none">';
          $form[$field_name]['#field_suffix'] = '</div>';
        }
        elseif (isset($field['#type']) && $field['#type'] == 'fieldset') {
          $form[$field_name]['address']['#prefix'] = '<div class="d-none">';
          $form[$field_name]['address']['#suffix'] = '</div>';
        }
        else {
          $form[$field_name]['#prefix'] = '<div class="d-none">';
          $form[$field_name]['#suffix'] = '</div>';
        }
      }
    }
  }

  /**
   * Submit function for the page 1 of the form.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function submitPageOne(array &$form, FormStateInterface $form_state) : void {
    $page_values = $this->saveAllCurrentInformation($form_state);
    $form_state
      ->setValues($page_values)
      ->set('page_values', $page_values)
      ->set('page', 2)
      ->setRebuild(TRUE);
  }

  /**
   * Function to back one page at the form.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function pageBack(array &$form, FormStateInterface $form_state) : void {
    $page_values = $this->saveAllCurrentInformation($form_state);
    $form_state
      ->setValues($page_values)
      ->set('page_values', $page_values)
      ->set('page', 1)
      ->setRebuild(TRUE);
  }

  /**
   * Validate if the field company are empty.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function validateEmptyFieldCompany(array &$form, FormStateInterface $form_state) : void {
    $company = $form_state->getValue('field_company');
    if ($company) {
      $form_state->setErrorByName('field_company', $this->t('If you want to register a new company this field should be empty.'));
    }
  }

  /**
   * Submit function for the page 2 of the form.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function submitPageTwo(array &$form, FormStateInterface $form_state) : void {
    $page_values = $this->saveAllCurrentInformation($form_state);
    $form_state
      ->setValues($page_values)
      ->set('page_values', $page_values);
  }

  /**
   * Function to save all data submitted by the form.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   Array with the values of the data submitted at the current page.
   */
  private function saveAllCurrentInformation(FormStateInterface &$form_state) : array {
    if ($form_state->get('page') == 1) {
      $form_state
        ->set('selectedAdministrativeArea', $form_state->getValue('administrativeArea', ''))
        ->set('selectedLocality', $form_state->getValue('locality', ''));
    }

    $page_values = $form_state->has('page_values') ? $form_state->get('page_values') : [];

    $page_values['field_telephone'] = $form_state->getValue('field_telephone', '');
    $page_values['field_telephone_extension'] = $form_state->getValue('field_telephone_extension', '');
    $page_values['field_phone'] = $form_state->getValue('field_phone', '');

    $page_values['field_address'] = $form_state->getValue('field_address', '');

    $page_values['administrativeArea'] = $form_state->getValue('administrativeArea', '');
    $page_values['locality'] = $form_state->getValue('locality', '');

    $page_values['field_company'] = $form_state->getValue('field_company', '');
    $page_values['field_company_role'] = $form_state->getValue('field_company_role', '');

    return $page_values;
  }

}
