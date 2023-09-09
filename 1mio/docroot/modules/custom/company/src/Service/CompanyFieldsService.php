<?php

namespace Drupal\company\Service;

use Drupal\address\Repository\SubdivisionRepository;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Implements the construction of the fields of the company in form.
 */
class CompanyFieldsService {

  use DependencySerializationTrait;
  use StringTranslationTrait;

  /**
   * Define the subdivisionRepository.
   *
   * @var \Drupal\address\Repository\SubdivisionRepository
   */
  private $subdivisionRepository;

  /**
   * Define the entityTypeManager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  private $entityTypeManager;

  /**
   * Define the constructor.
   */
  final public function __construct(SubdivisionRepository $subdivisionRepository, EntityTypeManager $entityTypeManager) {

    $this->subdivisionRepository = $subdivisionRepository;
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) : self {

    $instance = new static(
      $container->get('address.subdivision_repository'),
      $container->get('entity_type.manager'),
    );

    return $instance;
  }

  /**
   * Creating the logo and cover fields.
   */
  public function getImagesFields(array $form, FormStateInterface $form_state): array {

    $field_company_logo_data = [];
    if (is_array($form_state->getValue('field_company_logo', 0))) {
      $field_company_logo = $form_state->getValue('field_company_logo', 0);
      $field_company_logo_id = 0;
      if (isset($field_company_logo['target_id'])) {
        $field_company_logo_id = $field_company_logo['target_id'];
      }
      elseif (isset($field_company_logo['0'])) {
        $field_company_logo_id = $field_company_logo['0'];
      }
      $field_company_logo_data = [$field_company_logo_id];
    }

    $form['field_company_logo'] = [
      '#type'                 => 'managed_file',
      '#upload_location'      => 'public://company/logo',
      '#upload_validators'    => [
        'file_validate_is_image'      => [],
        'file_validate_extensions'    => ['gif png jpg jpeg'],
        'file_validate_size'          => [2 ** 20 * 5],
      ],
      '#title'                => $this->t('Profile picture'),
      '#description'          => $this->t('Allowed extensions: @extensions', ['@extensions' => 'gif png jpg jpeg']),
      '#default_value'        => $field_company_logo_data,
      '#required'             => TRUE,
      '#attributes' => [
        'class' => [
          $form_state->getValue('field_opening') != 'new' ? 'image-preview' : '',
        ],
      ],
    ];

    $field_company_cover_data = [];
    if (is_array($form_state->getValue('field_company_cover', 0))) {
      $field_company_cover = $form_state->getValue('field_company_cover', 0);
      $field_company_cover_id = 0;
      if (isset($field_company_cover['target_id'])) {
        $field_company_cover_id = $field_company_cover['target_id'];
      }
      elseif (isset($field_company_cover['0'])) {
        $field_company_cover_id = $field_company_cover['0'];
      }
      $field_company_cover_data = [$field_company_cover_id];
    }

    $form['field_company_cover'] = [
      '#type'                 => 'managed_file',
      '#upload_location'      => 'public://company/cover',
      '#upload_validators'    => [
        'file_validate_is_image'      => [],
        'file_validate_extensions'    => ['gif png jpg jpeg'],
        'file_validate_size'          => [2 ** 20 * 5],
      ],
      '#title'                => $this->t('Cover picture'),
      '#description'          => $this->t('Allowed extensions: @extensions', ['@extensions' => 'gif png jpg jpeg']),
      '#default_value'        => $field_company_cover_data,
    ];

    return $form;
  }

  /**
   * Creating the commitment term fields.
   */
  public function getCommitmentTermFields(array $form, FormStateInterface $form_state): array {
    $form['field_download_commitment_term_infos'] = [
      '#type' => 'markup',
      '#parents' => ['form'],
      '#markup' => '<p class="text-align-center text-muted" >Baixe o termo de compromisso acima e use o espaço abaixo para carregá-lo na plataforma após assina-lo.</p>',
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    $form['field_download_commitment_term_infos'] = [
      '#type' => 'markup',
    ];

    $field_company_commitment_term_data = [];
    if (is_array($form_state->getValue('field_company_commitment_term', 0))) {
      $field_company_commitment_term = $form_state->getValue('field_company_commitment_term', 0);
      $field_company_commitment_term_id = 0;
      if (isset($field_company_commitment_term['target_id'])) {
        $field_company_commitment_term_id = $field_company_commitment_term['target_id'];
      }
      elseif (isset($field_company_commitment_term['0'])) {
        $field_company_commitment_term_id = $field_company_commitment_term['0'];
      }
      $field_company_commitment_term_data = [$field_company_commitment_term_id];
    }

    $form['field_company_commitment_term'] = [
      '#type'                 => 'managed_file',
      '#upload_location'      => 'public://company/commitment_term',
      '#upload_validators'    => [
        'file_validate_extensions'    => ['pdf docx'],
        // 4 * 1024 * 1024 = 4194304bytes = 4MB.
        'file_validate_size'          => [4194304],
      ],
      '#title'                => $this->t("Commitment term"),
      '#description'          => $this->t('Allowed extensions: @extensions', ['@extensions' => 'pdf, docx']),
      '#default_value'        => $field_company_commitment_term_data,
    ];

    $form['field_company_commitment_term_infos'] = [
      '#type' => 'markup',
      '#parents' => ['form'],
      '#markup' => '<br><br><p class="text-align-center text-muted" >Ou se preferir, você pode deixar pra executar essa ação depois. Contudo, a avaliação de sua empresa pelo 1MiO só ocorrerá após o envio do termo.</p>',
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
   * @param bool $withNames
   *   Allows the method to return the company name fields.
   */
  public function createCompanyBranchFields(array $form, FormStateInterface $form_state, bool $withNames = FALSE): array {

    $form = $this->createCompanyFields($form, $form_state);
    if (!$withNames) {
      if ($form['field_company_type']['#default_value'] == "civil-society") {
        unset($form['field_company_corporate_name']);
      }
      unset($form['field_company_name']);
      unset($form['field_company_organization_name']);
    }

    unset($form['field_company_segment']);
    unset($form['field_company_branch_activity']);
    unset($form['field_company_start_activity']);
    unset($form['field_company_scope_operation']);
    unset($form['field_company_size']);
    unset($form['field_company_profile']);
    unset($form['field_company_public_check']);
    unset($form['field_company_mission_statement']);
    unset($form['field_company_values_statement']);
    return $form;
  }

  /**
   * Creating common fields in company/civil-society.
   *
   * @param array $form
   *   Define the form variable array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Define the $form_state variable.
   */
  public function createCompanyFields(array $form, FormStateInterface $form_state): array {

    $taxonomies = $this->getTaxonomyTerms();

    $form['field_company_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Type of Institution'),
      '#options' => [
        '' => 'Selecione...',
        'company' => $this->t('Company'),
        'civil-society' => $this->t('Civil society'),
      ],
      '#default_value' => $form_state->getValue('field_company_type', ''),
      '#disabled' => $form_state->getValue('field_opening') != 'new' ? 'disabled' : '',
    ];

    $group_type_company_state = [
      'visible' => [
        'select[name=field_company_type]' => ['value' => 'company'],
      ],
    ];

    $form['title'] = [
      '#type' => 'textfield',
      '#title' => $form_state->getValue('field_company_type', '') == 'company' ? $this->t('Company name') : $this->t('Organization name'),
      '#default_value' => $form_state->getValue('title', ''),
      '#maxlength' => 100,
    ];

    $form['field_company_name'] = [
      '#states' => $group_type_company_state,
      '#type' => 'textfield',
      '#title' => $this->t('Company name'),
      '#default_value' => $form_state->getValue('field_company_name', ''),
      '#maxlength' => 100,
    ];

    $form['field_company_cnpj'] = [
      '#states' => $group_type_company_state,
      '#type' => 'textfield',
      '#attributes' => [
        'class' => [
          'cnpj-mask',
        ],
      ],
      '#title' => $this->t('CNPJ'),
      '#default_value' => $form_state->getValue('field_company_cnpj', ''),
    ];

    $form['field_company_corporate_name'] = [
      '#states' => $group_type_company_state,
      '#type' => 'textfield',
      '#title' => $this->t('Corporate name'),
      '#default_value' => $form_state->getValue('field_company_corporate_name', ''),
      '#maxlength' => 100,
    ];

    $form['field_company_segment'] = [
      '#states' => $group_type_company_state,
      '#type' => 'select',
      '#options' => $taxonomies['company_segment'],

      '#title' => $this->t('Segment'),
      '#default_value' => $form_state->getValue('field_company_segment', ''),
    ];

    $form['field_company_branch_activity'] = [
      '#states' => $group_type_company_state,
      '#type' => 'select',
      '#options' => $taxonomies['company_branch_activity'],

      '#title' => $this->t('Activity area'),
      '#default_value' => $form_state->getValue('field_company_branch_activity', ''),
    ];

    $form['field_company_size'] = [
      '#states' => $group_type_company_state,
      '#type' => 'select',
      '#options' => $taxonomies['company_size'],

      '#title' => $this->t('Company size'),
      '#default_value' => $form_state->getValue('field_company_size', ''),
    ];

    $page_type_civil_society_state = [
      'visible' => [
        'select[name=field_company_type]' => ['value' => 'civil-society'],
      ],
    ];

    $form['field_company_organization_name'] = [
      '#states' => $page_type_civil_society_state,
      '#type' => 'textfield',
      '#title' => $this->t('Organization name'),
      '#default_value' => $form_state->getValue('field_company_organization_name', ''),
      '#maxlength' => 100,
    ];

    $form['field_company_start_activity'] = [
      '#states' => $page_type_civil_society_state,
      '#type' => 'date',
      '#title' => $this->t('Start date of activities'),
      '#default_value' => $form_state->getValue('field_company_start_activity', ''),
    ];

    $form['field_company_scope_operation'] = [
      '#states' => $page_type_civil_society_state,
      '#type' => 'select',
      '#options' => $taxonomies['company_scope_operation'],

      '#title' => $this->t('Company scope'),
      '#default_value' => $form_state->getValue('field_company_scope_operation', ''),
    ];

    $form['field_company_profile'] = [
      '#states' => $page_type_civil_society_state,
      '#type' => 'select',
      '#options' => $taxonomies['company_profile'],

      '#title' => $this->t('Which of the profiles below match the organization?'),
      '#default_value' => $form_state->getValue('field_company_profile', ''),
    ];

    $form['field_company_public_check'] = [
      '#states' => $page_type_civil_society_state,
      '#type' => 'checkboxes',
      '#options' => $taxonomies['company_public'],
      '#title' => $this->t('Does the initiative serve one or more public audiences below?'),
      '#default_value' => $form_state->getValue('field_company_public_check', []),
    ];

    $page_type_extra_fields_state = [
      'visible' => [
        'select[name=field_company_type]' => ['!value' => ''],
      ],
    ];

    $form['field_company_email'] = [
      '#states' => $page_type_extra_fields_state,
      '#type' => 'email',
      '#title' => $this->t('Main Email'),
      '#default_value' => $form_state->getValue('field_company_email', ''),
      '#required' => TRUE,
    ];

    $form['field_company_second_email'] = [
      '#states' => $page_type_extra_fields_state,
      '#type' => 'email',
      '#title' => $this->t('Alternative e-mail'),
      '#default_value' => $form_state->getValue('field_company_second_email', ''),
    ];

    $form['field_company_telephone'] = [
      '#states' => $page_type_extra_fields_state,
      '#type' => 'tel',
      '#attributes' => [
        'class' => [
          'telephone-mask',
        ],
      ],
      '#title' => $this->t('Landline'),
      '#default_value' => $form_state->getValue('field_company_telephone', ''),
    ];

    $form['field_company_telephone_extension'] = [
      '#states' => $page_type_extra_fields_state,
      '#type' => 'textfield',
      '#attributes' => [
        'class' => [
          'telephone-extension-mask',
        ],
      ],
      '#title' => $this->t('Phone extension'),
      '#default_value' => $form_state->getValue('field_company_telephone_extension', ''),
    ];

    $form['field_company_phone'] = [
      '#states' => $page_type_extra_fields_state,
      '#type' => 'tel',
      '#attributes' => [
        'class' => [
          'phone-mask',
        ],
      ],
      '#title' => $this->t('Cell Phone'),
      '#default_value' => $form_state->getValue('field_company_phone', ''),
    ];

    $form['field_company_mission_statement'] = [
      '#states' => $page_type_extra_fields_state,
      '#type' => 'textarea',
      '#title' => $this->t('Mission'),
      '#default_value' => $form_state->getValue('field_company_mission_statement', ''),
      '#maxlength' => 500,
    ];

    $form['field_company_values_statement'] = [
      '#states' => $page_type_extra_fields_state,
      '#type' => 'textarea',
      '#title' => $this->t('Values'),
      '#default_value' => $form_state->getValue('field_company_values_statement', ''),
      '#maxlength' => 500,
    ];

    $form['field_company_address']['#type'] = 'fieldset';

    $form['field_company_address']['addressLine1'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Street Address'),
      '#default_value' => $form_state->getValue('addressLine1', ''),
      '#maxlength' => 50,
    ];

    $form['field_company_address']['dependentLocality'] = [
      '#type' => 'textfield',
      '#title' => $this->t('District'),
      '#maxlength' => 50,
      '#default_value' => $form_state->getValue('dependentLocality', ''),

    ];

    $states = $this->subdivisionRepository->getList(['BR']);
    $states = array_merge(['' => $this->t('Select...')], $states);

    $form['field_company_address']['administrativeArea'] = [
      '#type' => 'select',
      '#title' => $this->t('Administrative area'),
      '#default_value' => '',
      '#options' => $states,
      '#ajax' => [
        'callback' => [$this, 'reloadLocalitySelect'],
        'event' => 'change',
      ],
    ];

    $form['field_company_address']['locality'] = [
      '#type' => 'select',
      '#title' => $this->t('City'),
      '#id' => "locality-select",
      '#default_value' => $form_state->getValue('locality', ''),
      '#prefix' => '<div id="form-item-locality-pre">',
      '#suffix' => '</div>',
      '#attributes' => [
        'placeholder' => ('First select state'),
      ],
      '#validated' => TRUE,
    ];

    $form['field_company_address']['postalCode'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Postal code'),
      '#attributes' => [
        'class' => [
          'postal-code-mask',
        ],
      ],
      '#default_value' => $form_state->getValue('postalCode', ''),
    ];

    return $form;

  }

  /**
   * Define the TaxonomyTerms that will be used.
   *
   * @return array
   *   Return a array with the taxonomy terms.
   */
  private function getTaxonomyTerms() : array {

    $taxonomy_terms = [];
    $used_taxonomies = [
      'company_scope_operation',
      'vacancy_benefits',
      'company_profile',
      'company_size',
      'company_public',
      'company_branch_activity',
      'company_segment',
    ];

    $checkboxes = [
      'company_public',
    ];

    foreach ($used_taxonomies as $used_taxonomy) {
      $terms = $this->entityTypeManager
        ->getStorage('taxonomy_term')
        ->loadTree($used_taxonomy);

      if (!in_array($used_taxonomy, $checkboxes)) {
        $taxonomy_terms[$used_taxonomy][''] = $this->t("Select...");
      }

      foreach ($terms as $term) {
        $taxonomy_terms[$used_taxonomy][$term->tid] = $term->name;
      }

    }

    return $taxonomy_terms;
  }

  /**
   * Callback ajax.
   *
   * Selects and returns the cities of the state.
   */
  public function reloadLocalitySelect(array &$form, FormStateInterface $form_state) : AjaxResponse {
    $selected_area = $form_state->getUserInput()['administrativeArea'];
    if ($form_state->getValue('field_opening') == 'new') {
      $selected_area = $form_state->getUserInput()['field_company_address']['administrativeArea'];
    }

    $cities = @$this->subdivisionRepository->getList(['BR', $selected_area]);
    $cities = array_merge(['' => $this->t('Select...')], $cities);

    if (isset($selected_area) && !empty($selected_area)) {
      $form['field_company_address']['locality']['#disabled'] = '';
      $form['field_company_address']['locality']['#options'] = $cities;

    }

    $form_state->setRebuild();
    $response = new AjaxResponse();
    $response->addCommand(new ReplaceCommand("#form-item-locality-pre", ($form['field_company_address']['locality'])));
    $response->addCommand(new InvokeCommand('', 'select2ReRun', ["#locality-select"]));
    return $response;

  }

}
