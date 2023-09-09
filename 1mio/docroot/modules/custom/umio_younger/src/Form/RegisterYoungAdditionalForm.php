<?php

namespace Drupal\umio_younger\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\umio_helpers\Service\FormValidator;
use Drupal\user\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Implements the Young additional fields Form.
 */
class RegisterYoungAdditionalForm extends FormBase {

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
   * Define the constructor.
   */
  final public function __construct(FormValidator $formValidator, AccountInterface $user) {

    $this->formValidator = $formValidator;
    $this->user = $user;

  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) : FormBase {

    $instance = new static(
      $container->get('umio_helpers.form_validator'),
      $container->get('current_user')
    );

    $instance->subdivisionRepository = $container->get('address.subdivision_repository');
    $instance->entityTypeManager = $container->get('entity_type.manager');

    return $instance;

  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'register_young_additional_form';
  }

  /**
   * Adding the title of the page.
   */
  public function getPageTitleMarkup(int $page) : string {
    $markup = '';
    $current_user = \Drupal::currentUser();

    /** @var \Drupal\user\Entity\User $user*/
    $user = User::load($current_user->id());

    $data = [
      1 => [
        '@title' => "<i class='ph-user-bold me-3'></i>Oi, {$user->get('field_user_name')->getString()} ",
        '@description' => 'Informe seus dados pessoais:',
      ],
    ];

    $markup = '
    <div class="form-page-title">
      <span class="page-title-title d-flex align-items-center">@title</span>
      <span class="page-title-description">@description</span>
    </div>';

    $markup = str_replace(
      ['@title', '@description'],
      [$data[$page]['@title'], $data[$page]['@description']],
      $markup
    );

    return $markup;
  }

  /**
   * Adding the subtitle of the page.
   */
  public function getPageInfoMarkup(int $page) : string {
    $markup = '';

    $data = [
      1 => [
        '@icon' => 'ph-user',
        '@text' => 'Dados pessoais',
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
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    if (!$form_state->has('page')) {
      $form_state->set('page', 1);
    }

    $page = $form_state->get('page');

    $form['page_title'] = [
      '#type' => 'markup',
      '#parents' => ['form'],
      '#markup' => $this->getPageTitleMarkup($page),
    ];

    $form['page_info'] = [
      '#type' => 'markup',
      '#parents' => ['form'],
      '#markup' => $this->getPageInfoMarkup($page),
    ];

    $form['field_user_cpf'] = [
      '#type' => 'textfield',
      '#attributes' => [
        'class' => [
          'cpf-mask',
        ],
      ],
      '#title' => $this->t('CPF'),
      '#default_value' => $form_state->getValue('field_user_cpf', ''),
      '#required' => TRUE,
    ];

    $form['field_user_gender'] = [
      '#type' => 'select_or_other_select',
      '#title' => t('Gender'),
      '#default_value' => [''],
      '#options' => [
        'female' => t('Female'),
        'male' => t('Male'),
        'non-binary' => t('Non binary'),
      ],
      '#other' => t('Other'),
      '#required' => TRUE,
      '#multiple' => FALSE,
      '#other_unknown_defaults' => 'ignore',
      '#other_delimiter' => FALSE,
      '#select_type' => 'checkboxes',
    ];

    $form['field_user_phone'] = [
      '#type' => 'tel',
      '#attributes' => [
        'class' => [
          'phone-mask',
        ],
      ],
      '#title' => $this->t('Phone for contact'),
      '#default_value' => $form_state->getValue('field_company_telephone', ''),
      '#required' => TRUE,
    ];

    $states = @$this->subdivisionRepository->getList(['BR']);
    $states = array_merge([0 => $this->t('Select...')], $states);
    $state = @$form_state->getUserInput()['administrativeArea'];

    $form['field_user_address']['administrativeArea'] = [
      '#type' => 'select',
      '#title' => $this->t('Administrative area'),
      '#default_value' => $form_state->getValue('administrativeArea', ''),
      '#options' => $states,
      '#ajax' => [
        'callback' => [$this, 'reloadLocalitySelect'],
        'event' => 'change',
      ],
      '#attributes' => [
        'class' => [
          'select2-without-allow-clear',
        ],
      ],
      '#required' => TRUE,
    ];

    $form['field_user_address']['locality'] = [
      '#type' => 'select',
      '#title' => $this->t('City'),
      '#prefix' => '<div id="form-item-locality-pre">',
      '#suffix' => '</div>',
      '#name' => 'locality',
      '#default_value' => $form_state->getValue('locality', ''),
      '#validated' => TRUE,
      '#attributes' => [
        'id' => 'locality-select',
        'class' => [
          'select2-without-allow-clear',
        ],
      ],
      '#required' => TRUE,
    ];

    if ($state) {

      $cities = @$this->subdivisionRepository->getList(['BR', $state]);
      $cities = array_merge([0 => $this->t('Select...')], $cities);

      $form['field_user_address']['locality']['#options'] = $cities;
      $form['field_user_address']['locality']['#disabled'] = '';

    }
    else {
      $form['field_user_address']['locality']['#disabled'] = 'disabled';
    }

    $form['actions'] = [
      '#type' => 'actions',
    ];

    $form['actions']['back'] = [
      '#type' => 'submit',
      '#value' => $this->t('Back'),
      '#submit' => ['::pageBack'],
      '#limit_validation_errors' => [],
      '#attributes' => [
        'class' => ['btn-previous', 'btn'],
      ],
    ];

    $form['actions']['next'] = [
      '#type' => 'submit',
      '#button_type' => 'primary',
      '#value' => $this->t('Continue'),
      '#submit' => ['::submitForm'],
      '#validate' => ['::validateForm'],
      '#attributes' => [
        'class' => ['btn-next', 'btn', 'btn-primary'],
      ],
    ];

    $form['page_current_step'] = [
      '#type' => 'hidden',
      '#title' => $this->t('page_current_step'),
      '#value' => $page + 2,
    ];
    return $form;
  }

  /**
   * Ajax callback for return the cities of the current Administrative area.
   */
  public function reloadLocalitySelect(array &$form, FormStateInterface $form_state) : AjaxResponse {

    $selected_area = $form_state->getValue('administrativeArea');
    $response = new AjaxResponse();

    if (isset($selected_area) && !empty($selected_area)) {
      $cities = $this->subdivisionRepository->getList(['BR', $selected_area]);
      $cities = array_merge([0 => $this->t('Select...')], $cities);
      $form['field_user_address']['locality'] = [
        '#type' => 'select',
        '#title' => $this->t('City'),
        '#prefix' => '<div id="form-item-locality-pre">',
        '#suffix' => '</div>',
        '#name' => 'locality',
        '#options' => $cities,
        '#id' => "locality-select",
        '#validated' => TRUE,
        '#attributes' => [
          'data-drupal-selector' => "edit-locality",
          'data-select2-id' => "edit-locality",
          'class' => [
            'form-select ',
            'valid',
          ],
        ],
        '#default_value' => '',
        '#required' => TRUE,
      ];

      $response->addCommand(new ReplaceCommand("#form-item-locality-pre", ($form['field_user_address']['locality'])));
      $response->addCommand(new InvokeCommand('', 'select2ReRun', ['#locality-select']));
    }
    return $response;

  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    $cpf = $form_state->getValue('field_user_cpf');
    $phone = $form_state->getValue('field_user_phone');
    if (!$this->formValidator->validateCpf($cpf)) {
      $form_state->setErrorByName('field_user_cpf', $this->t('This field must be a valid CPF.'));
    }
    if ($phone && !$this->formValidator->validatePhone($phone)) {
      $form_state->setErrorByName('field_user_phone', $this->t('This field must be a valid phone.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $youngUser = User::load($this->user->id());
    $cpf = $form_state->getValue('field_user_cpf');
    $phone = $form_state->getValue('field_user_phone');
    $administrativeArea = $form_state->getValue('administrativeArea');
    $locality = $form_state->getValue('locality');
    $fieldUserGender = $form_state->getValue('field_user_gender');
    $paragraph = Paragraph::create([
      'type' => 'paragraph_address',
      'field_paragraph_address' => [
        'langcode' => '',
        'country_code' => 'BR',
        'address_line1' => '',
        'dependent_locality' => '',
        'administrative_area' => $administrativeArea,
        'locality' => $locality,
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
    $youngUser->set('field_user_address', $paragraphCreated);
    $youngUser->set('field_user_cpf', $cpf);
    $youngUser->set('field_user_phone', $phone);
    $youngUser->set('field_user_gender', $fieldUserGender);
    // This flag informs that users completed their registration.
    $youngUser->set('field_flag_singup', TRUE);
    if ($youngUser->save()) {
      $form_state->setRedirect('umio_younger.register_additional_young_success');
    }
  }

}
