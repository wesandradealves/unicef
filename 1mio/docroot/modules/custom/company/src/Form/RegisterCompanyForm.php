<?php

namespace Drupal\company\Form;

use Drupal\company\Service\CompanyFieldsService;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\umio_helpers\Service\FormValidator;
use Drupal\user\Entity\User;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Implements an example form.
 */
class RegisterCompanyForm extends FormBase {

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
  protected $requiredMark = [
    'field_company_name',
    'field_company_organization_name',
    'field_company_public_check',
    'field_company_type',
  ];

  /**
   * Define the page1 objects.
   *
   * @var array
   */
  protected $page1 = [
    'field_company_logo',
    'field_company_cover',
  ];

  /**
   * Define the page2 objects.
   *
   * @var array
   */
  protected $page2 = [
    'field_company_type',
    'field_company_name',
    'field_company_cnpj',
    'field_company_corporate_name',
    'field_company_segment',
    'field_company_branch_activity',
    'field_company_size',
    'field_company_organization_name',
    'field_company_start_activity',
    'field_company_scope_operation',
    'field_company_profile',
    'field_company_public_check',
    'field_company_email',
    'field_company_second_email',
    'field_company_telephone',
    'field_company_telephone_extension',
    'field_company_phone',
    'field_company_mission_statement',
    'field_company_values_statement',
  ];

  /**
   * Define the page3 objects.
   *
   * @var array
   */
  protected $page3 = [
    'field_company_address',
  ];

  /**
   * Define the page4 objects.
   *
   * @var array
   */
  protected $page4 = [
    'field_company_commitment_term',
    'field_download_commitment_term_infos',
    'field_download_commitment_term',
    'field_company_commitment_term_infos',
  ];

  /**
   * Define the page5 objects.
   *
   * @var array
   */
  protected $page5 = [];

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
   * Define the entityTypeManager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

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
  final public function __construct(FormValidator $formValidator, AccountInterface $user, CompanyFieldsService $companyFieldsService) {

    $this->formValidator = $formValidator;
    $this->user = $user;
    $this->companyFieldsService = $companyFieldsService;

  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) : FormBase {

    $instance = new static(
      $container->get('umio_helpers.form_validator'),
      $container->get('current_user'),
      $container->get('company.company_fields')
    );

    $instance->subdivisionRepository = $container->get('address.subdivision_repository');
    $instance->entityTypeManager = $container->get('entity_type.manager');
    $instance->companyFieldsService = $container->get('company.company_fields');

    return $instance;

  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'register_company_form';
  }

  /**
   * Form constructor.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array|RedirectResponse
   *   The form structure.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $this->setFormDefaultFields($form, $form_state);

    if ($form_state->has('page') && $form_state->get('page') == 5) {
      $commitment_term = $form_state->getValue('field_commitment_term', '');
      if (isset($commitment_term) && !empty($commitment_term)) {
        $url = Url::fromRoute('company.success_register')->toString();
        return new RedirectResponse($url);
      }
      else {
        $url = Url::fromRoute('company.almost_success_register')->toString();
        return new RedirectResponse($url);
      }
    }

    if ($form_state->has('page') && $form_state->get('page') == 4) {
      return $this->formPageButtonsFour($form, $form_state);
    }

    if ($form_state->has('page') && $form_state->get('page') == 3) {
      return $this->formPageButtonsThree($form, $form_state);
    }

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
  public function formPageButtonsOne(array &$form, FormStateInterface $form_state) : array {

    $form['actions']['empty_space'] = [
      '#type' => 'markup',
      '#markup' => '<span></span>',
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

    $field_company_logo = $form_state->getValue('field_company_logo');
    if (!isset($field_company_logo) || empty($field_company_logo)) {
      $form_state->setErrorByName('field_company_logo', $this->t('The company logo is required.'));
    }
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
      '#submit' => ['::submitPageTwo'],
      '#validate' => ['::validatePageTwo'],
      '#attributes' => [
        'class' => ['btn-next', 'btn', 'btn-primary'],
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

    $field_company_type = $form_state->getValue('field_company_type');
    if (!isset($field_company_type) || empty($field_company_type)) {
      $form_state->setErrorByName('field_company_type', $this->t('The company type is required.'));
    }

    if ($field_company_type == 'company') {
      $field_company_name = $form_state->getValue('field_company_name');
      if (!isset($field_company_name) || empty($field_company_name)) {
        $form_state->setErrorByName('field_company_name', $this->t('The Company name is required.'));
      }
    }

    if ($field_company_type == 'civil-society') {
      $field_company_organization_name = $form_state->getValue('field_company_organization_name');
      if (!isset($field_company_organization_name) || empty($field_company_organization_name)) {
        $form_state->setErrorByName('field_company_organization_name', $this->t('The Organization name is required.'));
      }

      $field_company_public_check = $form_state->getValue('field_company_public_check');
      $field_company_public_check_selection = array_sum($field_company_public_check);
      if (!$field_company_public_check_selection) {
        $form_state->setErrorByName('field_company_public_check', $this->t('The company public is required.'));
      }

    }

    $field_company_email = $form_state->getValue('field_company_email');
    if (!isset($field_company_email) || empty($field_company_email)) {
      $form_state->setErrorByName('field_company_email', $this->t('The company e-mail is required.'));
    }

    $company_type = $form_state->getValue('field_company_type');

    switch ($company_type) {
      case 'company':
        $name = $form_state->getValue('field_company_name');
        $cnpj = $form_state->getValue('field_company_cnpj');

        if (!isset($name) || empty($name)) {
          $form_state->setErrorByName('field_company_name', $this->t('This field must be the company name.'));
        }

        if ($cnpj && !$this->formValidator->validateCnpj($cnpj)) {
          $form_state->setErrorByName('field_company_cnpj', $this->t('This field must be a valid CNPJ.'));
        }
        else {
          $uids = $this->entityTypeManager
            ->getStorage('node')
            ->getQuery()
            ->condition('field_company_cnpj', $cnpj)
            ->condition('type', 'company')
            ->execute();
          if (!empty($uids)) {
            $form_state->setErrorByName('field_company_cnpj', $this->t('This CNPJ is already registrated.'));
          }
          unset($uids);
        }
        break;

      case 'civil-society':
        $organization_name = $form_state->getValue('field_company_organization_name');
        if (!isset($organization_name) || empty($organization_name)) {
          $form_state->setErrorByName('field_company_organization_name', $this->t('This field must be the company name.'));
        }
        break;

      default:
        $form_state->setErrorByName('field_company_type', $this->t('This field must be selected.'));
        break;

    }

    $mail = $form_state->getValue('field_company_email');
    if ($mail && !$this->formValidator->validateEmail($mail)) {

      $form_state->setErrorByName('field_company_email', $this->t('This field must be a valid e-mail.'));

    }
    else {
      $uids = $this->entityTypeManager
        ->getStorage('node')
        ->getQuery()
        ->condition('field_company_email', $mail)
        ->condition('type', 'company')
        ->execute();

      if (!empty($uids)) {
        $form_state->setErrorByName('field_company_email', $this->t('This e-mail is already registrated.'));
      }
      unset($uids);

    }

    $alternative_mail = $form_state->getValue('field_company_second_email');
    if ($alternative_mail && !$this->formValidator->validateEmail($alternative_mail)) {
      $form_state->setErrorByName('field_company_second_email', $this->t('This field must be a valid e-mail.'));
    }

    $telephone = $form_state->getValue('field_company_telephone');
    if ($telephone && !$this->formValidator->validateTelephone($telephone)) {
      $form_state->setErrorByName('field_company_telephone', $this->t('This field must be a valid telephone.'));
    }

    $telephoneExtension = $form_state->getValue('field_company_telephone_extension');
    if ($telephoneExtension && !is_numeric($telephoneExtension)) {
      $form_state->setErrorByName('field_company_telephone_extension', $this->t('This field must be a valid telephone extension.'));
    }

    $phone = $form_state->getValue('field_company_phone');
    if ($phone && !$this->formValidator->validatePhone($phone)) {
      $form_state->setErrorByName('field_company_phone', $this->t('This field must be a valid phone.'));
    }
  }

  /**
   * Custom submit for the current page.
   *
   * @param array $form
   *   Define the form variable array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Define the $form_state variable.
   */
  public function submitPageTwo(array &$form, FormStateInterface $form_state) : void {
    $page_values = $this->saveAllCurrentInformation($form_state);
    $form_state
      ->setValues($page_values)
      ->set('page_values', $page_values)
      ->set('page', 3)
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
  public function formPageButtonsThree(array &$form, FormStateInterface $form_state) : array {
    $form['actions']['back'] = [
      '#type' => 'submit',
      '#value' => $this->t('Back'),
      '#submit' => ['::pageThreeBack'],
      '#limit_validation_errors' => [],
      '#attributes' => [
        'class' => ['btn-previous', 'btn'],
      ],
    ];

    $form['actions']['next'] = [
      '#type' => 'submit',
      '#button_type' => 'primary',
      '#value' => $this->t('Next'),
      '#submit' => ['::submitPageThree'],
      '#validate' => ['::validatePageThree'],
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
  public function pageThreeBack(array &$form, FormStateInterface $form_state) : void {
    $page_values = $this->saveAllCurrentInformation($form_state);
    $form_state
      ->setValues($page_values)
      ->set('page_values', $page_values)
      ->set('page', 2)
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
  public function validatePageThree(array &$form, FormStateInterface $form_state) : void {
    $postalCode = $form_state->getValue('field_company_address')['postalCode'];
    if ($postalCode && !$this->formValidator->validatePostalCode($postalCode)) {
      $form_state->setErrorByName('field_company_address][postalCode', $this->t('This field must be a valid postal code.'));
    }
  }

  /**
   * Custom submit for the current page.
   *
   * @param array $form
   *   Define the form variable array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Define the $form_state variable.
   */
  public function submitPageThree(array &$form, FormStateInterface $form_state) : void {
    $page_values = $this->saveAllCurrentInformation($form_state);
    $form_state
      ->setValues($page_values)
      ->set('page_values', $page_values)
      ->set('page', 4)
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
  public function formPageButtonsFour(array &$form, FormStateInterface $form_state) : array {
    $form['actions']['back'] = [
      '#type' => 'submit',
      '#value' => $this->t('Back'),
      '#submit' => ['::pageFourBack'],
      '#limit_validation_errors' => [],
      '#attributes' => [
        'class' => ['btn-previous', 'btn', 'btn-primary'],
      ],
    ];
    $form['actions']['next'] = [
      '#type' => 'submit',
      '#button_type' => 'primary',
      '#value' => $this->t('Next'),
      '#submit' => ['::submitPageFour', '::submitForm'],
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
  public function pageFourBack(array &$form, FormStateInterface $form_state) : void {
    $page_values = $this->saveAllCurrentInformation($form_state);
    $form_state
      ->setValues($page_values)
      ->set('page_values', $page_values)
      ->set('page', 3)
      ->setRebuild(TRUE);
  }

  /**
   * Custom submit for the current page.
   *
   * @param array $form
   *   Define the form variable array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Define the $form_state variable.
   */
  public function submitPageFour(array &$form, FormStateInterface $form_state) : void {
    $page_values = $this->saveAllCurrentInformation($form_state);
    $form_state
      ->setValues($page_values)
      ->set('page_values', $page_values)
      ->set('page', 4)
      ->setRebuild(TRUE);
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
        '@title' => 'Empresa',
        '@description' => 'Escolha imagens que representem sua organização.',
      ],
      2 => [
        '@title' => 'Empresa',
        '@description' => 'Legal! Agora vamos para os dados da sua empresa.',
      ],
      3 => [
        '@title' => 'Empresa',
        '@description' => 'E onde fica a sua empresa?',
      ],
      4 => [
        '@title' => 'Empresa',
        '@description' => 'Falta pouco! Agora vamos para o termo de compromisso.',
      ],
    ];

    $markup = '
    <div class="form-page-title">
      <div class="page-icon-title">
        <span class="page-info-icon">
          <i class="ph-hard-drives"></i>
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
        '@icon' => 'ph-image',
        '@text' => 'Imagens',
      ],
      2 => [
        '@icon' => 'ph-hard-drives',
        '@text' => 'Dados da empresa',
      ],
      3 => [
        '@icon' => 'ph-map-pin',
        '@text' => 'Local da sede',
      ],
      4 => [
        '@icon' => 'ph-note-pencil',
        '@text' => 'Termo de compromisso',
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

    $form['#attributes']['enctype'] = 'multipart/form-data';

    $form_state->setValue('field_opening', 'new');

    $form = $this->companyFieldsService->getImagesFields($form, $form_state);
    $form = $this->companyFieldsService->createCompanyFields($form, $form_state);
    unset($form['title']);

    $form['field_company_address']['administrativeArea']['#required'] = TRUE;
    $form['field_company_address']['locality']['#required'] = TRUE;

    $form = $this->companyFieldsService->getCommitmentTermFields($form, $form_state);

    $companyConfig = $this->config('company.settings');
    $form['field_download_commitment_term_infos']['#markup'] = $companyConfig->get('commitment_term_markup');

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
        if (isset($field['#type']) && $field['#type'] == 'fieldset') {
          foreach ($form[$field_name] as $key => $fieldset) {
            if ($key === '#type') {
              continue;
            }
            if (isset($form[$field_name][$key]['#required'])) {
              unset($form[$field_name][$key]['#required']);
            }
          }
        }
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

    $page_values = $form_state->has('page_values') ? $form_state->get('page_values') : [];

    $page_values['field_company_logo'] = $form_state->getValue('field_company_logo', 0);
    $page_values['field_company_cover'] = $form_state->getValue('field_company_cover', 0);

    $page_values['field_company_type'] = $form_state->getValue('field_company_type', '');
    $page_values['field_company_name'] = $form_state->getValue('field_company_name', '');
    $page_values['field_company_cnpj'] = $form_state->getValue('field_company_cnpj', '');
    $page_values['field_company_corporate_name'] = $form_state->getValue('field_company_corporate_name', '');
    $page_values['field_company_segment'] = $form_state->getValue('field_company_segment', '');
    $page_values['field_company_branch_activity'] = $form_state->getValue('field_company_branch_activity', '');
    $page_values['field_company_size'] = $form_state->getValue('field_company_size', '');
    $page_values['field_company_organization_name'] = $form_state->getValue('field_company_organization_name', '');
    $page_values['field_company_start_activity'] = $form_state->getValue('field_company_start_activity', '');
    $page_values['field_company_scope_operation'] = $form_state->getValue('field_company_scope_operation', '');
    $page_values['field_company_profile'] = $form_state->getValue('field_company_profile', '');
    $page_values['field_company_public_check'] = $form_state->getValue('field_company_public_check', []);
    $page_values['field_company_email'] = $form_state->getValue('field_company_email', '');
    $page_values['field_company_second_email'] = $form_state->getValue('field_company_second_email', '');
    $page_values['field_company_telephone'] = $form_state->getValue('field_company_telephone', '');
    $page_values['field_company_telephone_extension'] = $form_state->getValue('field_company_telephone_extension', '');
    $page_values['field_company_phone'] = $form_state->getValue('field_company_phone', '');
    $page_values['field_company_mission_statement'] = $form_state->getValue('field_company_mission_statement', '');
    $page_values['field_company_values_statement'] = $form_state->getValue('field_company_values_statement', '');
    $page_values['field_company_address'] = $form_state->getValue('field_company_address', '');
    if (isset($page_values['field_company_address']['locality'])) {
      $page_values['locality'] = $page_values['field_company_address']['locality'];
    }
    $page_values['field_company_commitment_term'] = $form_state->getValue('field_company_commitment_term', 0);

    return $page_values;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) : void {
    $form_state->set('page', 5);
    $companyMainId = $this->submitMainCompany($form, $form_state);
    $this->updateUserCompany($companyMainId);
  }

  /**
   * Save the main company.
   */
  public function submitMainCompany(array &$form, FormStateInterface $form_state) : int {
    $data = $form_state->getValues();

    $company_type = $form_state->getValue('field_company_type');
    $company_data = ['type' => 'company'];
    $company_object = '';

    $company_data['field_company_logo'] = $data['field_company_logo'];
    $company_data['field_company_cover'] = $data['field_company_cover'];

    $company_data['field_company_type'] = $data['field_company_type'];

    switch ($company_type) {
      case 'company':

        $company_object = 'Company';

        $company_data['title'] = $data['field_company_name'];
        $company_data['field_company_corporate_name'] = $data['field_company_corporate_name'];

        $company_data['field_company_size'] = $data['field_company_size'];
        $company_data['field_company_branch_activity'] = $data['field_company_branch_activity'];

        $company_data['field_company_cnpj'] = $data['field_company_cnpj'];
        $company_data['field_company_segment'] = $data['field_company_segment'];

        break;

      case 'civil-society':
        $company_object = 'Civil Society';
        $company_data['title'] = $data['field_company_organization_name'];

        $company_data['field_company_start_activity'] = date('Y-m-d', strtotime($data['field_company_start_activity']));
        $company_data['field_company_public'] = array_filter($data['field_company_public_check']);
        $company_data['field_company_profile'] = $data['field_company_profile'];
        $company_data['field_company_scope_operation'] = $data['field_company_scope_operation'];

        break;

    }

    $company_data['field_company_email'] = $data['field_company_email'];
    $company_data['field_company_second_email'] = $data['field_company_second_email'];

    $company_data['field_company_telephone'] = $data['field_company_telephone'];
    $company_data['field_company_phone_extension'] = $data['field_company_telephone_extension'];

    $company_data['field_company_phone'] = $data['field_company_phone'];

    $company_data['field_company_mission_statement'] = substr($data['field_company_mission_statement'], 0, 500);
    $company_data['field_company_values_statement'] = substr($data['field_company_values_statement'], 0, 500);

    $commitment_term = $company_data['field_company_commitment_term'] = $data['field_company_commitment_term'];

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
          "locality" => $data['locality'],
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
    return $company->id();
  }

  /**
   * Save the user's company.
   *
   * Saving the company recently created to user.
   *
   * @param int $companyMainId
   *   Defines the ID of the company where the user is based.
   */
  private function updateUserCompany(int $companyMainId) : void {
    $currentUser = User::load($this->user->id());
    $currentUser->set('field_user_company', $companyMainId);

    // This flag informs that users completed their registration.
    $currentUser->set('field_flag_singup', TRUE);

    $currentUser->save();
  }

}
