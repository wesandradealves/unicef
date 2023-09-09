<?php

namespace Drupal\company\Form;

use Drupal\company\Service\CompanyFieldsService;
use Drupal\company\Service\CompanyService;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\company\Service\CompanyFormValidator;
use Drupal\user\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Implements the branch register.
 */
class RegisterCompanyBranchForm extends FormBase {

  /**
   * Define the default objects of the page.
   *
   * @var array
   */
  protected $pageDefaults = [
    '#attributes',
    'page_title',
    'page_info',
    'actions',
  ];

  /**
   * Define the objects with required mark.
   *
   * @var array
   */
  protected $requiredMark = [];

  /**
   * Define the page1 objects.
   *
   * @var array
   */
  protected $page1 = [
    'field_company_main_office',
    'field_company_type',
    'field_company_name',
    'field_company_organization_name',
    'field_company_corporate_name',
    'field_company_cnpj',
    'field_company_email',
    'field_company_second_email',
    'field_company_telephone',
    'field_company_telephone_extension',
    'field_company_phone',
  ];

  /**
   * Define the page2 objects.
   *
   * @var array
   */
  protected $page2 = [
    'field_company_address',
  ];

  /**
   * Define the page3 objects.
   *
   * @var array
   */
  protected $page3 = [];

  /**
   * Define the FormValidator.
   *
   * @var \Drupal\company\Service\CompanyFormValidator
   */
  protected $companyFormValidator;

  /**
   * Define the subdivisionRepository.
   *
   * @var \Drupal\address\Repository\SubdivisionRepository
   */
  protected $subdivisionRepository;

  /**
   * Define the entityTypeManager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * Company service helper.
   *
   * @var \Drupal\company\Service\CompanyService
   */
  protected $companyService;

  /**
   * Define the FormValidator.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $user;

  /**
   * Define the companyFieldsService.
   *
   * @var \Drupal\company\Service\CompanyFieldsService
   */
  protected $companyFieldsService;

  /**
   * Define the constructor.
   */
  final public function __construct(
    CompanyService $companyService,
    CompanyFormValidator $companyFormValidator,
    AccountInterface $user,
    CompanyFieldsService $companyFieldsService) {

    $this->companyFormValidator = $companyFormValidator;
    $this->user = $user;
    $this->companyFieldsService = $companyFieldsService;
    $this->companyService = $companyService;

  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {

    $instance = new static(
      $container->get('company.company_service'),
      $container->get('company.form_validator'),
      $container->get('current_user'),
      $container->get('company.company_fields'),
    );

    $instance->subdivisionRepository = $container->get('address.subdivision_repository');
    $instance->entityTypeManager = $container->get('entity_type.manager');

    return $instance;

  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'register_company_branch_form';
  }

  /**
   * Form constructor.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The form structure.
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {

    $this->setFormDefaultFields($form, $form_state);

    if ($form_state->has('page') && $form_state->get('page') == 2) {
      return $this->formPageButtonsTwo($form, $form_state);
    }

    return $this->formPageButtonsOne($form, $form_state);

  }

  /**
   * Define the custom action buttons for the page..
   *
   * @param array $form
   *   Define the form variable array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Define the $form_state variable.
   *
   * @return array
   *   Return a array with the button markup.
   */
  public function formPageButtonsOne(array &$form, FormStateInterface $form_state): array {
    $form['actions']['back'] = [
      '#type' => 'submit',
      '#value' => $this->t('Back'),
      '#submit' => ['::pageOneBack'],
      '#limit_validation_errors' => [],
      '#attributes' => [
        'class' => ['btn-previous', 'btn', 'd-none'],
      ],
    ];

    $form['actions']['next'] = [
      '#type' => 'submit',
      '#button_type' => 'primary',
      '#value' => $this->t('Next'),
      '#submit' => ['::submitPageOne'],
      '#validate' => ['::validatePageOne'],
      '#attributes' => [
        'class' => ['btn-next', 'btn', 'btn-primary'],
      ],
    ];

    return $form;

  }

  /**
   * Define the custom validatePage function.
   *
   * @param array $form
   *   Define the form variable array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Define the $form_state variable.
   */
  public function validatePageOne(array &$form, FormStateInterface $form_state) : void {
    $this->companyFormValidator->validateForm($form_state);
  }

  /**
   * Custom submit for the current page.
   *
   * @param array $form
   *   Define the form variable array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Define the $form_state variable.
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
   * Define the custom action buttons for the page..
   *
   * @param array $form
   *   Define the form variable array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Define the $form_state variable.
   *
   * @return array
   *   Return a array with the button markup.
   */
  public function formPageButtonsTwo(array &$form, FormStateInterface $form_state) : array {

    $form['actions']['back'] = [
      '#type' => 'submit',
      '#value' => $this->t('Back'),
      '#submit' => ['::pageTwoBack'],
      '#limit_validation_errors' => [],
      '#attributes' => [
        'class' => ['btn-previous', 'btn'],
      ],
    ];

    $form['actions']['next'] = [
      '#type' => 'submit',
      '#button_type' => 'primary',
      '#value' => $this->t('Next'),
      '#submit' => ['::submitForm'],
      '#validate' => ['::validatePageTwo'],
      '#attributes' => [
        'class' => ['btn-submit', 'btn', 'btn-primary'],
      ],
    ];

    return $form;
  }

  /**
   * Define the custom pageBack function.
   *
   * @param array $form
   *   Define the form variable array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Define the $form_state variable.
   */
  public function pageTwoBack(array &$form, FormStateInterface $form_state) : void {
    $page_values = $this->saveAllCurrentInformation($form_state);
    $form_state
      ->setValues($page_values)
      ->set('page_values', $page_values)
      ->set('page', 1)
      ->setRebuild(TRUE);
  }

  /**
   * Define the custom validatePage function.
   *
   * @param array $form
   *   Define the form variable array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Define the $form_state variable.
   */
  public function validatePageTwo(array &$form, FormStateInterface $form_state) : void {
    $postalCode = $form_state->getValue('field_company_address')['postalCode'];
    if ($postalCode && !$this->companyFormValidator->formValidator->validatePostalCode($postalCode)) {
      $form_state->setErrorByName('field_company_address][postalCode', $this->t('This field must be a valid postal code.'));
    }
  }

  /**
   * Define the info on the title.
   *
   * @param int $page
   *   Define the current page number.
   *
   * @return string
   *   Return the markup to be shown as title.
   */
  public function getPageTitleMarkup(int $page) : string {
    $markup = '';

    $data = [
      1 => [
        '@title' => (string) $this->t('Branch'),
        '@description' => (string) $this->t('Cool! lets take some information about the branch.'),
      ],
      2 => [
        '@title' => (string) $this->t('Branch'),
        '@description' => (string) $this->t('And where is the branch located?'),
      ],
    ];

    $markup = '
    <div class="form-page-title">
      <div class="page-icon-title">
        <span class="page-info-icon">
          <i class="ph-buildings"></i>
        </span>
        <span class="page-title-title">@title</span>
      </div>
      <span class="page-title-description page-title-description col-12 col-lg-8">@description</span>
    </div>';

    $markup = str_replace(
      ['@title', '@description'],
      [$data[$page]['@title'], $data[$page]['@description']],
      $markup
    );

    return $markup;
  }

  /**
   * Define the info on the SubTitle.
   *
   * @param int $page
   *   Define the current page number.
   *
   * @return string
   *   Return the markup to be shown as SubTitle information.
   */
  public function getPageInfoMarkup(int $page) : string {
    $markup = '';

    $data = [
      1 => [
        '@icon' => 'ph-hard-drives',
        '@text' => (string) $this->t('Branch data'),
      ],
      2 => [
        '@icon' => 'ph-buildings',
        '@text' => (string) $this->t('Region'),
      ],
    ];

    $markup = '
    <div class="form-page-info">
      <span class="page-info-icon">
        <i class="@icon svg-white"></i>
      </span>
      <span class="page-info-text">@text</span>
    </div>';

    $markup = str_replace(
      ['@icon', '@text'],
      [$data[$page]['@icon'], $data[$page]['@text']],
      $markup
    );

    return $markup;
  }

  /**
   * Returns an array with the active companies to use in select field.
   *
   * @return array
   *   Array with active companies options.
   */
  protected function getCompaniesOptions(): array {
    $companies = [];
    $companies[''] = $this->t("Select...");
    $companies = $companies + $this->companyService->getActiveCompanies();
    return $companies;
  }

  /**
   * Returns the company type from the main company.
   *
   * @var \Drupal\node\Entity\Node|null $company
   *  Define the company to be researched.
   *
   * @return string
   *   The company type as string.
   */
  protected function getCompanyType(?Node $company): string {
    if ($company === NULL) {
      return '';
    }
    $mainOfficeCompanyType = '';
    $mainOfficeCompany = $this->companyService->getMainBranchFromCompany($company);
    if ($mainOfficeCompany) {
      $mainOfficeCompanyType = Node::load($mainOfficeCompany)->get('field_company_type')->getString();
    }
    return $mainOfficeCompanyType;
  }

  /**
   * Put all the fields on the page.
   *
   * @param array $form
   *   Define the form variable array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Define the $form_state variable.
   */
  public function setFormDefaultFields(array &$form, FormStateInterface $form_state) : void {

    if (!$form_state->has('page')) {
      $form_state->set('page', 1);
    }

    $loggedUserSelectedCompany = NULL;
    if (!$form_state->getValue('field_company_main_office')) {
      $loggedUser = User::load($this->user->id());
      if ($loggedUser->get('field_user_company')->getString()) {
        $loggedUserSelectedCompany = Node::load($loggedUser->get('field_user_company')->getString());
      }
    }
    elseif ($form_state->getValue('field_company_main_office')) {
      $loggedUserSelectedCompany = Node::load($form_state->getValue('field_company_main_office'));
    }

    $page = $form_state->get('page');

    $form['#tree'] = TRUE;

    $form['page_title'] = [
      '#type' => 'markup',
      '#parents' => ['form'],
      '#markup' => @$this->getPageTitleMarkup($page),
    ];

    $form['page_info'] = [
      '#type' => 'markup',
      '#parents' => ['form'],
      '#markup' => @$this->getPageInfoMarkup($page),
    ];

    $form['field_company_main_office'] = [
      '#type' => 'select',
      '#title' => $this->t('Company'),
      '#options' => $this->getCompaniesOptions(),
      '#default_value' => !empty($loggedUserSelectedCompany) ? $loggedUserSelectedCompany->id() : NULL,
      '#attributes' => [
        'class' => [
          'select2-without-allow-clear',
        ],
      ],
      '#ajax' => [
        'callback' => [$this, 'reloadCompanyTypeSelect'],
        'event' => 'change',
      ],
    ];

    $form['#attributes']['enctype'] = 'multipart/form-data';

    $form_state->setValue('field_opening', 'new');

    $form = $this->companyFieldsService->createCompanyBranchFields($form, $form_state, TRUE);

    $form['field_company_type']['#disabled'] = TRUE;
    $form['field_company_type']['#default_value'] = $this->getCompanyType($loggedUserSelectedCompany);
    $form['field_company_type']['#value'] = $this->getCompanyType($loggedUserSelectedCompany);
    $form['field_company_type']['#prefix'] = '<div id="edit-output">';
    $form['field_company_type']['#suffix'] = '</div>';

    $form['field_company_main_office']['#required'] = TRUE;
    $form['field_company_type']['#required'] = TRUE;

    $statesTypeCompany = [
      'select[name=field_company_type]' => [
        "value" => "company",
      ],
    ];
    $form['field_company_name']['#states']['required'] = $statesTypeCompany;
    $form['field_company_cnpj']['#states']['required'] = $statesTypeCompany;
    $form['field_company_corporate_name']['#states']['required'] = $statesTypeCompany;

    $statesTypeCivilSociety = [
      'select[name=field_company_type]' => [
        "value" => "civil-society",
      ],
    ];
    $form['field_company_organization_name']['#states']['required'] = $statesTypeCivilSociety;

    unset($form['title']);

    $states = @$this->subdivisionRepository->getList(['BR']);
    $states = array_merge([0 => $this->t('Select...')], $states);

    $form['page_current_step'] = [
      '#type' => 'hidden',
      '#title' => $this->t('page_current_step'),
      '#value' => $page - 1,
    ];

    $arr_btn_actions = [];

    if ($page != 1) {
      $arr_btn_actions = [
        'container-buttons',
        'col-lg-10',
        'justify-content-between',
      ];
    }
    else {
      $arr_btn_actions = [
        'container-buttons',
        'col-lg-10',
      ];
    }

    $form['actions'] = [
      '#type' => 'actions',
      '#attributes' => [
        'class' => $arr_btn_actions,
      ],
    ];

    $this->defineFieldsExhibition($form, $form_state);
  }

  /**
   * Define the fields shown by the page number $page.
   *
   * @param array $form
   *   Define the form variable array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Define the $form_state variable.
   */
  public function defineFieldsExhibition(array &$form, FormStateInterface $form_state) : void {

    $page = $form_state->get('page');

    foreach ($form as $field_name => $field) {

      if (!in_array($field_name, $this->{'page' . $page}) && !in_array($field_name, $this->pageDefaults)) {

        if (isset($form[$field_name]['#required'])) {
          unset($form[$field_name]['#required']);
        }

        if (isset($field['#type']) && $field['#type'] == 'managed_file') {

          unset($form[$field_name]['#title']);
          unset($form[$field_name]['#description']);

          $form[$field_name]['#field_prefix'] = '<div class="d-none">';
          $form[$field_name]['#field_suffix'] = '</div>';

        }
        else {

          if ($field_name != '#tree') {
            $form[$field_name]['#prefix'] = '<div class="d-none">';
            $form[$field_name]['#suffix'] = '</div>';
          }

        }

      }

      else {

        if (in_array($field_name, $this->requiredMark)) {

          if ($field['#type'] == 'checkboxes') {
            $form[$field_name]['#prefix'] = '<div class="legend-form-required">';
            $form[$field_name]['#suffix'] = '</div>';
          }

          $form[$field_name]['#label_attributes'] = ['class' => 'form-required'];

        }

      }

    }

  }

  /**
   * Return a array with the updated current $page_values from form_state.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Define the $form_state variable.
   *
   * @return array
   *   Return the filled array to keep the data saved.
   */
  public function saveAllCurrentInformation(FormStateInterface &$form_state) : array {

    $page = $form_state->has('page') ? $form_state->get('page') : 1;
    $page_values = $form_state->has('page_values') ? $form_state->get('page_values') : [];

    if ($page == 1) {
      $page_values['field_company_main_office'] = $form_state->getValue('field_company_main_office', '');
      $page_values['field_company_type'] = $form_state->getValue('field_company_type', '');
      $page_values['field_company_name'] = $form_state->getValue('field_company_name', '');
      $page_values['field_company_cnpj'] = $form_state->getValue('field_company_cnpj', '');
      $page_values['field_company_corporate_name'] = $form_state->getValue('field_company_corporate_name', '');
      $page_values['field_company_organization_name'] = $form_state->getValue('field_company_organization_name', '');
      $page_values['field_company_start_activity'] = $form_state->getValue('field_company_start_activity', '');
      $page_values['field_company_email'] = $form_state->getValue('field_company_email', '');
      $page_values['field_company_second_email'] = $form_state->getValue('field_company_second_email', '');
      $page_values['field_company_telephone'] = $form_state->getValue('field_company_telephone', '');
      $page_values['field_company_telephone_extension'] = $form_state->getValue('field_company_telephone_extension', '');
      $page_values['field_company_phone'] = $form_state->getValue('field_company_phone', '');
    }
    if ($page == 2) {
      $page_values['field_company_address'] = $form_state->getValue('field_company_address', '');
    }

    return $page_values;

  }

  /**
   * Callback ajax.
   *
   * Selects and returns the cities of the state.
   */
  public function reloadLocalitySelect(array &$form, FormStateInterface $form_state): AjaxResponse {
    $selected_area = $form_state->getValue('field_company_address')['administrativeArea'];

    $cities = @$this->subdivisionRepository->getList(['BR', $selected_area]);
    $cities = array_merge([0 => $this->t('Select...')], $cities);

    $form['field_company_address']['locality'] = [
      '#type' => 'select',
      '#name' => "field_company_address[locality]",
      '#title' => $this->t('City'),
      '#prefix' => '<div id="form-item-locality-pre">',
      '#suffix' => '</div>',
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
    ];

    if (isset($selected_area) && !empty($selected_area)) {

      $form['field_company_address']['locality']['#disabled'] = '';
      $form['field_company_address']['locality']['#options'] = $cities;

    }

    $response = new AjaxResponse();
    $response->addCommand(new ReplaceCommand("#form-item-locality-pre", ($form['field_company_address']['locality'])));
    $response->addCommand(new InvokeCommand('', 'select2ReRun', ["#locality-select"]));
    return $response;
  }

  /**
   * Callback ajax: change the value of the company type select.
   *
   * @param array $form
   *   Define the form variable array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Define the $form_state variable.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   Define the response to the Drupal's ajax receiver.
   */
  public function reloadCompanyTypeSelect(array &$form, FormStateInterface &$form_state): AjaxResponse {
    $loggedUserSelectedCompany = $form_state->getValues()['field_company_main_office'];
    $loggedUserSelectedCompanyType = Node::load($loggedUserSelectedCompany)->get('field_company_type')->getString();

    $form['field_company_type']['#value'] = $loggedUserSelectedCompanyType;
    $form_state->setValue('field_company_type', $loggedUserSelectedCompanyType);

    $response = new AjaxResponse();
    $response->addCommand(new ReplaceCommand("#edit-output", ($form['field_company_type'])));
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) : void {
    $form_state->set('page', 3);
    // Update user main company.
    $currentUser = User::load($this->user->id());
    $selectedCompany = $form_state->getValues()['field_company_main_office'];
    if ($selectedCompany) {
      $selectedCompany = Node::load($selectedCompany);
      $mainOfficeCompany = Node::load($this->companyService->getMainBranchFromCompany($selectedCompany));
      if ($mainOfficeCompany) {
        $mainOfficeCompanySharedData = [
          'field_company_main_office' => $mainOfficeCompany->id(),
          'field_company_logo' => $mainOfficeCompany->get('field_company_logo')->getValue(),
          'field_company_cover' => $mainOfficeCompany->get('field_company_cover')->getValue(),
          'field_company_commitment_term' => $mainOfficeCompany->get('field_company_commitment_term')->getValue(),
          'field_company_type' => $mainOfficeCompany->get('field_company_type')->getString(),
          'field_company_start_activity' => $mainOfficeCompany->get('field_company_start_activity')->getString(),
          'field_company_branch_activity' => $mainOfficeCompany->get('field_company_branch_activity')->getValue(),
          'field_company_scope_operation' => $mainOfficeCompany->get('field_company_scope_operation')->getValue(),
          'field_company_size' => $mainOfficeCompany->get('field_company_size')->getValue(),
          'field_company_segment' => $mainOfficeCompany->get('field_company_segment')->getValue(),
          'field_company_profile' => $mainOfficeCompany->get('field_company_profile')->getValue(),
          'field_company_public' => $mainOfficeCompany->get('field_company_public')->getValue(),
          'field_company_mission_statement' => $mainOfficeCompany->get('field_company_mission_statement')->getString(),
          'field_company_values_statement' => $mainOfficeCompany->get('field_company_values_statement')->getString(),
        ];
        $form_state->set('company_shared_data', $mainOfficeCompanySharedData);
      }
      $currentUser->set('field_user_company', $mainOfficeCompany->id());

      // This flag informs that users completed their registration.
      $currentUser->set('field_flag_singup', TRUE);

      $currentUser->save();
    }
    $this->submitBranchCompany($form, $form_state);
  }

  /**
   * Save the main company.
   */
  public function submitBranchCompany(array &$form, FormStateInterface $form_state): void {
    $data = $form_state->getValues();
    $sharedData = $form_state->get('company_shared_data');
    $company_type = $form_state->getValue('field_company_type');
    $company_data = ['type' => 'company'];
    $company_object = '';

    $company_data['field_company_main_office'] = $sharedData['field_company_main_office'];
    $company_data['field_company_logo'] = $sharedData['field_company_logo'];
    $company_data['field_company_cover'] = $sharedData['field_company_cover'];
    $company_data['field_company_type'] = $data['field_company_type'];
    switch ($company_type) {
      case 'company':
        $company_object = 'Company';
        $company_data['title'] = $data['field_company_name'];
        $company_data['field_company_cnpj'] = $data['field_company_cnpj'];
        $company_data['field_company_corporate_name'] = $data['field_company_corporate_name'];
        $company_data['field_company_size'] = $sharedData['field_company_size'];
        $company_data['field_company_branch_activity'] = $sharedData['field_company_branch_activity'];
        $company_data['field_company_segment'] = $sharedData['field_company_segment'];
        break;

      case 'civil-society':
        $company_object = 'Civil Society';
        $company_data['title'] = $data['field_company_organization_name'];
        $company_data['field_company_start_activity'] = date('Y-m-d', strtotime($sharedData['field_company_start_activity']));
        $company_data['field_company_public'] = array_filter($sharedData['field_company_public']);
        $company_data['field_company_profile'] = $sharedData['field_company_profile'];
        $company_data['field_company_scope_operation'] = $sharedData['field_company_scope_operation'];
        break;
    }
    $company_data['field_company_email'] = $data['field_company_email'];
    $company_data['field_company_second_email'] = $data['field_company_second_email'];
    $company_data['field_company_telephone'] = $data['field_company_telephone'];
    $company_data['field_company_phone_extension'] = $data['field_company_telephone_extension'];
    $company_data['field_company_phone'] = $data['field_company_phone'];
    $company_data['field_company_mission_statement'] = $sharedData['field_company_mission_statement'];
    $company_data['field_company_values_statement'] = $sharedData['field_company_values_statement'];
    $commitment_term = $company_data['field_company_commitment_term'] = $sharedData['field_company_commitment_term'];
    $company = Node::create($company_data);

    if (isset($commitment_term) && !empty($commitment_term)) {
      $company->set('moderation_state', 'pending_approval');
    }

    if (!$company->save()) {
      $this->messenger()->addError($this->t("An error occurred trying save the @company_object.", ['@company_object' => $company_object]));
    }
    else {
      $nodeCompany = Node::load($company->id());
      $paragraph = Paragraph::create([
        'type' => 'paragraph_address',
        'field_paragraph_address' => [
          "langcode" => "",
          "country_code" => "BR",
          "administrative_area" => $data['field_company_address']['administrativeArea'],
          "locality" => $data['field_company_address']['locality'],
          "dependent_locality" => $data['field_company_address']['dependentLocality'],
          "postal_code" => $data['field_company_address']['postalCode'],
          "address_line1" => $data['field_company_address']['addressLine1'],
        ],
      ]);
      $paragraph->save();
      $nodeCompany->field_company_address = [
        [
          'target_id' => $paragraph->id(),
          'target_revision_id' => $paragraph->getRevisionId(),
        ],
      ];
      $nodeCompany->save();
    }
    $form_state->setRedirect('company.success_register');
  }

}
