<?php

namespace Drupal\umio_admin_area\Form;

use Drupal\company\Service\CompanyFieldsService;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\umio_helpers\Service\FormValidator;
use Drupal\umio_user\Service\UserService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a Company form.
 */
class CompanyBranchFormEdit extends FormBase {

  use DependencySerializationTrait;

  /**
   * Define the entityTypeManager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * Define the companyFieldsService.
   *
   * @var \Drupal\company\Service\CompanyFieldsService
   */
  protected $companyFieldsService;

  /**
   * Define the FormValidator.
   *
   * @var \Drupal\umio_helpers\Service\FormValidator
   */
  protected $formValidator;

  /**
   * Define the company content type.
   *
   * @var \Drupal\node\Entity\Node|string
   */
  protected $company;

  /**
   * Define the FormValidator.
   *
   * @var \Drupal\umio_user\Service\UserService
   */
  private $userService;

  /**
   * Define the form type.
   *
   * @var string
   */
  protected $formType;

  /**
   * Define the Main Company data.
   *
   * @var \Drupal\node\Entity\Node
   */
  private $userMainCompany;

  /**
   * {@inheritdoc}
   */
  final public function __construct(FormValidator $formValidator, CompanyFieldsService $companyFieldsService, EntityTypeManager $entityTypeManager, UserService $userService) {
    $this->formValidator = $formValidator;
    $this->companyFieldsService = $companyFieldsService;
    $this->entityTypeManager = $entityTypeManager;
    $this->userService = $userService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new static(
      $container->get('umio_helpers.form_validator'),
      $container->get('company.company_fields'),
      $container->get('entity_type.manager'),
      $container->get('umio_user.user_service'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'company_branch_edit_form';
  }

  /**
   * Set fields value in company/civil-society.
   *
   * @param array $form
   *   Define the form variable array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Define the $form_state variable.
   */
  public function setDefaultValues(array $form, FormStateInterface $form_state): void {
    $userMainCompanyId = $this->userService->getCurrentUserMainCompany();
    $this->userMainCompany = Node::load($userMainCompanyId);

    if (!is_numeric($this->company)) {
      $this->formType = 'add';
      $this->company = Node::create([
        'type' => 'company',
        'field_company_type' => $this->userMainCompany->get('field_company_type')->getString(),
      ]);
      $form_state->setValue('field_opening', 'add');
    }
    else {
      $this->formType = 'edit';
      $this->company = Node::load($this->company);
      $form_state->setValue('field_opening', 'edit');
    }

    if (!$this->company) {
      return;
    }

    /** @var \Drupal\Core\Field\FieldItemListInterface  */
    $companyFields = $this->company->getFields();

    $companyType = $companyFields['field_company_type']->getString();

    $form_state->setValue('title', $companyFields['title']->getString());

    $form_state->setValue('field_company_type', $companyType);
    $form_state->setValue('field_company_cnpj', $companyFields['field_company_cnpj']->getString());
    if ($companyType == 'company') {
      $form_state->setValue('field_company_corporate_name', $companyFields['field_company_corporate_name']->getString());
    }
    $form_state->setValue('field_company_segment', $companyFields['field_company_segment']->target_id);
    $form_state->setValue('field_company_branch_activity', $companyFields['field_company_branch_activity']->target_id);
    $form_state->setValue('field_company_size', $companyFields['field_company_size']->target_id);
    $form_state->setValue('field_company_email', $companyFields['field_company_email']->getString());
    $form_state->setValue('field_company_second_email', $companyFields['field_company_second_email']->getString());
    $form_state->setValue('field_company_telephone', $companyFields['field_company_telephone']->getString());
    $form_state->setValue('field_company_telephone_extension', $companyFields['field_company_phone_extension']->getString());
    $form_state->setValue('field_company_phone', $companyFields['field_company_phone']->getString());

    if ($companyFields['field_company_address']->getValue() != NULL) {
      $addressParagraph = Paragraph::load($companyFields['field_company_address']->target_id);
      $address = $addressParagraph->get('field_paragraph_address')->getValue()[0];
      $form_state->setValue('addressLine1', $address['address_line1']);
      $form_state->setValue('administrativeArea', $address['administrative_area']);
      $form_state->setValue('locality', $address['locality']);
      $form_state->setValue('dependentLocality', $address['dependent_locality']);
      $form_state->setValue('postalCode', $address['postal_code']);
    }
  }

  /**
   * Returns the page title based on the last route company ID.
   *
   * @return string
   *   Page Title.
   */
  public function vocabularyTitle(): string {
    $parameters = \Drupal::routeMatch()->getParameter('company');
    if (!is_numeric($parameters)) {
      return t('Register branch');
    }
    else {
      return t('Edit branch');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, string $company = NULL): array {
    $this->company = $company;
    $this->setDefaultValues($form, $form_state);
    $form = $this->companyFieldsService->createCompanyBranchFields($form, $form_state);
    if ($this->formType == 'edit') {
      $form['field_company_cnpj']['#disabled'] = 'disabled';
    }
    elseif ($this->formType == 'add') {
      $form['field_company_type']['#default_value'] = $this->userMainCompany->get('field_company_type')->getValue()[0]['value'];
    }
    unset($form['field_company_commitment_term_infos']);

    $form['title']['#required'] = TRUE;
    $form['field_company_main_office']['#required'] = TRUE;
    $form['field_company_type']['#required'] = TRUE;

    $statesTypeCompany = [
      'select[name=field_company_type]' => [
        "value" => "company",
      ],
    ];
    $form['field_company_cnpj']['#states']['required'] = $statesTypeCompany;
    $form['field_company_corporate_name']['#states']['required'] = $statesTypeCompany;

    $form['actions'] = [
      '#type' => 'actions',
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
    ];
    $form['field_company_telephone']['#prefix'] = '<div class="d-flex justify-content-between">';
    $form['field_company_telephone_extension']['#suffix'] = '</div>';
    $form['field_company_address']['administrativeArea']['#prefix'] = '<div class="d-flex justify-content-between">';
    $form['field_company_address']['locality']['#suffix'] = '</div></div>';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    $cnpj = $form_state->getValue('field_company_cnpj');
    $companyCNPJ = $this->company->get('field_company_cnpj')->getString();

    if ($cnpj && $cnpj !== $companyCNPJ && $this->formType === 'edit') {
      $this->messenger()->addError($this->t("Can't change the CNPJ for the company."));
    }

    $mail = $form_state->getValue('field_company_email');
    if ($mail && !$this->formValidator->validateEmail($mail)) {
      $form_state->setErrorByName('field_company_email', $this->t('This field must be a valid e-mail.'));
    }
    elseif ($mail != $this->company->get('field_company_email')->getString()) {
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

    $postalCode = $form_state->getValue('postalCode');
    if ($postalCode && !$this->formValidator->validatePostalCode($postalCode)) {
      $form_state->setErrorByName('field_company_address][postalCode', $this->t('This field must be a valid postal code.'));
    }

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $companyType = $form_state->getValue('field_company_type');

    $this->company->set('title', $form_state->getValue('title'));

    if ($companyType == 'company') {
      $this->company->set('field_company_corporate_name', $form_state->getValue('field_company_corporate_name'));
      $company_object = 'Company';
    }
    else {
      $company_object = 'Civil Society';
    }

    $this->company->set('field_company_email', $form_state->getValue('field_company_email'));
    $this->company->set('field_company_second_email', $form_state->getValue('field_company_second_email'));
    $this->company->set('field_company_telephone', $form_state->getValue('field_company_telephone'));
    $this->company->set('field_company_phone_extension', $form_state->getValue('field_company_telephone_extension'));
    $this->company->set('field_company_phone', $form_state->getValue('field_company_phone'));

    if ($this->formType === 'add') {

      // Allow to insert the CNPJ.
      $this->company->set('field_company_cnpj', $form_state->getValue('field_company_cnpj'));

      // Set the basic data obtained from the main company.
      $this->company->set('field_company_main_office', $this->userMainCompany->id());
      $this->company->set('field_company_logo', $this->userMainCompany->get('field_company_logo')->getValue());
      $this->company->set('field_company_cover', $this->userMainCompany->get('field_company_cover')->getValue());
      $this->company->set('field_company_type', $this->userMainCompany->get('field_company_type')->getString());
      $this->company->set('field_company_start_activity', $this->userMainCompany->get('field_company_start_activity')->getString());
      $this->company->set('field_company_branch_activity', $this->userMainCompany->get('field_company_branch_activity')->getValue());
      $this->company->set('field_company_scope_operation', $this->userMainCompany->get('field_company_scope_operation')->getValue());
      $this->company->set('field_company_size', $this->userMainCompany->get('field_company_size')->getValue());
      $this->company->set('field_company_segment', $this->userMainCompany->get('field_company_segment')->getValue());
      $this->company->set('field_company_profile', $this->userMainCompany->get('field_company_profile')->getValue());
      $this->company->set('field_company_public', $this->userMainCompany->get('field_company_public')->getValue());
      $this->company->set('field_company_mission_statement', $this->userMainCompany->get('field_company_mission_statement')->getString());
      $this->company->set('field_company_values_statement', $this->userMainCompany->get('field_company_values_statement')->getString());

      $mainModerationCurrent = $this->userMainCompany->get('moderation_state')->getString();
      if ($mainModerationCurrent != 'draft') {
        $this->company->set('moderation_state', 'pending_approval');
      }
    }

    $addressNewValues = [
      "langcode" => "",
      "country_code" => "BR",
      'administrative_area' => $form_state->getValue('administrativeArea'),
      'locality' => $form_state->getValue('locality'),
      'dependent_locality' => $form_state->getValue('dependentLocality'),
      'address_line1' => $form_state->getValue('addressLine1'),
      'postal_code' => $form_state->getValue('postalCode'),
    ];

    $addressParagraph = $this->company->get('field_company_address')->getValue();
    if ($addressParagraph !== NULL && !empty($addressParagraph)) {
      $addressParagraph = Paragraph::load($this->company->get('field_company_address')->getValue()[0]['target_id']);
    }
    else {
      $addressNewValues['type'] = 'paragraph_address';
      $addressParagraph = Paragraph::create($addressNewValues);
    }

    $addressParagraph->set('field_paragraph_address', $addressNewValues);
    $addressParagraph->save();

    if ($this->company->save()) {
      $form_state->setRedirect('umio_admin_area.ta_admin.company.branches');
    }
    else {
      $this->messenger()->addError($this->t("An error occurred trying save the @company_object.", ['@company_object' => $company_object]));
    }
  }

}
