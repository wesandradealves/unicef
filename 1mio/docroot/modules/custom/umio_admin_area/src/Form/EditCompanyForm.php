<?php

namespace Drupal\umio_admin_area\Form;

use Drupal\company\Service\CityFieldsService;
use Drupal\company\Service\CompanyFieldsService;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\umio_helpers\Service\FormValidator;
use Drupal\user\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a Company form.
 */
class EditCompanyForm extends FormBase {

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
   * The city fields service.
   *
   * @var \Drupal\company\Service\CityFieldsService
   */
  protected $cityFieldsService;

  /**
   * Define the FormValidator.
   *
   * @var \Drupal\umio_helpers\Service\FormValidator
   */
  protected $formValidator;

  /**
   * Define the company content type.
   *
   * @var \Drupal\node\Entity\Node
   */
  protected $company;

  /**
   * Defines the type of Public Sector.
   *
   * @var array
   */
  protected $publicSectorType;

  /**
   * {@inheritdoc}
   */
  final public function __construct(FormValidator $formValidator, CompanyFieldsService $companyFieldsService, CityFieldsService $cityFieldsService, EntityTypeManager $entityTypeManager) {
    $this->formValidator = $formValidator;
    $this->companyFieldsService = $companyFieldsService;
    $this->cityFieldsService = $cityFieldsService;
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new static(
      $container->get('umio_helpers.form_validator'),
      $container->get('company.company_fields'),
      $container->get('company.city_fields_service'),
      $container->get('entity_type.manager'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'company_edit_company';
  }

  /**
   * Set fields value in company/civil-society.
   *
   * @param array $form
   *   Define the form variable array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Define the $form_state variable.
   */
  public function setDefaultValues(array $form, FormStateInterface $form_state):void {
    $currentUser = User::load($this->currentUser()->id());
    $this->company = Node::load($currentUser->get('field_user_company')->getString());
    $this->publicSectorType = [
      'city',
      'state',
    ];
    if (!$this->company) {
      return;
    }

    /** @var \Drupal\Core\Field\FieldItemListInterface  */
    $companyFields = $this->company->getFields();
    $form_state->setValue('field_opening', 'edit');
    $form_state->setValue('field_company_logo', $companyFields['field_company_logo']->getValue()[0]);
    $form_state->setValue('field_company_cover', $companyFields['field_company_cover']->getValue()[0]);
    $form_state->setValue('field_company_type', $companyFields['field_company_type']->getString());
    $form_state->setValue('field_company_cnpj', $companyFields['field_company_cnpj']->getString());

    $form_state->setValue('field_company_name', $companyFields['field_company_corporate_name']->getString());
    $form_state->setValue('field_company_corporate_name', $companyFields['field_company_corporate_name']->getString());

    $form_state->setValue('field_company_segment', $companyFields['field_company_segment']->target_id);
    $form_state->setValue('field_company_branch_activity', $companyFields['field_company_branch_activity']->target_id);
    $form_state->setValue('field_company_size', $companyFields['field_company_size']->target_id);

    $form_state->setValue('title', $companyFields['title']->getString());

    $form_state->setValue('field_company_start_activity', $companyFields['field_company_start_activity']->getString());
    $form_state->setValue('field_company_scope_operation', $companyFields['field_company_scope_operation']->target_id);
    $form_state->setValue('field_company_profile', $companyFields['field_company_profile']->target_id);
    if ($companyFields['field_company_public']->getValue()) {
      $ids = array_column($companyFields['field_company_public']->getValue(), 'target_id');
      $form_state->setValue('field_company_public_check', $ids);
    }

    $form_state->setValue('field_company_email', $companyFields['field_company_email']->getString());
    $form_state->setValue('field_company_second_email', $companyFields['field_company_second_email']->getString());
    $form_state->setValue('field_company_telephone', $companyFields['field_company_telephone']->getString());
    $form_state->setValue('field_company_telephone_extension', $companyFields['field_company_phone_extension']->getString());
    $form_state->setValue('field_company_phone', $companyFields['field_company_phone']->getString());
    $form_state->setValue('field_company_mission_statement', $companyFields['field_company_mission_statement']->getString());
    $form_state->setValue('field_company_values_statement', $companyFields['field_company_values_statement']->getString());

    if ($this->company->get('field_company_commitment_term')->getValue()) {
      $commitment_term = [$this->company->get('field_company_commitment_term')->getValue()[0]['target_id']];
      $form_state->setValue('field_company_commitment_term', $commitment_term);
    }

    if ($companyFields['field_company_address']->getValue() != NULL) {
      $addressParagraph = Paragraph::load($companyFields['field_company_address']->target_id);
      $address = $addressParagraph->get('field_paragraph_address')->getValue()[0];
      $form_state->setValue('addressLine1', $address['address_line1']);
      $form_state->setValue('administrativeArea', $address['administrative_area']);
      $form_state->setValue('locality', $address['locality']);
      $form_state->setValue('dependentLocality', $address['dependent_locality']);
      $form_state->setValue('postalCode', $address['postal_code']);
    }
    if (in_array($this->company->get('field_company_type')->getString(), $this->publicSectorType)) {
      $form_state->setValue('field_company_unicef_stamp', $companyFields['field_company_unicef_stamp']->getString());
      $form_state->setValue('field_company_region', $companyFields['field_company_region']->getString());
    }

  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $this->setDefaultValues($form, $form_state);
    $form = $this->companyFieldsService->getImagesFields($form, $form_state);
    if (in_array($this->company->get('field_company_type')->getString(), $this->publicSectorType)) {
      $form = $this->cityFieldsService->createCityFields($form, $form_state);
      $form['field_company_region']['#disabled'] = 'disabled';
      $form['field_company_unicef_stamp']['#disabled'] = 'disabled';
      unset($form['field_institution_type']);
      unset($form['field_company_address']);
    }
    else {
      $form = $this->companyFieldsService->createCompanyFields($form, $form_state);
    }
    unset($form['field_company_name']);
    unset($form['field_company_organization_name']);

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
    $form['field_company_cnpj']['#disabled'] = 'disabled';

    $form = $this->companyFieldsService->getCommitmentTermFields($form, $form_state);
    if ($this->company->get('field_company_unicef_stamp')->getString() == 1) {
      unset($form['field_company_commitment_term']);
    }
    unset($form['field_company_commitment_term_infos']);

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
    if ($cnpj && $cnpj !== $companyCNPJ) {
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

    $field_company_type = $form_state->getValue('field_company_type');
    if ($field_company_type == 'civil-society') {
      $field_company_public_check = $form_state->getValue('field_company_public_check');
      $field_company_public_check_selection = array_sum($field_company_public_check);
      if (!$field_company_public_check_selection) {
        $form_state->setErrorByName('field_company_public_check', $this->t('The company public is required.'));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $companyType = $form_state->getValue('field_company_type');
    $this->company->set('field_company_logo', $form_state->getValue('field_company_logo'));
    $this->company->set('field_company_cover', $form_state->getValue('field_company_cover'));
    if (!in_array($this->company->get('field_company_type')->getString(), $this->publicSectorType)) {
      $this->company->set('title', $form_state->getValue('title'));
    }
    switch ($companyType) {
      case 'company':
        $this->company->set('field_company_corporate_name', $form_state->getValue('field_company_corporate_name'));
        if ($segment = $form_state->getValue('field_company_segment')) {
          $this->company->set('field_company_segment', $segment);
        }
        if ($branchActivity = $form_state->getValue('field_company_branch_activity')) {
          $this->company->set('field_company_branch_activity', $branchActivity);
        }
        if ($size = $form_state->getValue('field_company_size')) {
          $this->company->set('field_company_size', $size);
        }
        break;

      case 'civil-society':
        $this->company->set('field_company_start_activity', $form_state->getValue('field_company_start_activity'));
        if ($scopeOperation = $form_state->getValue('field_company_scope_operation')) {
          $this->company->set('field_company_scope_operation', $scopeOperation);
        }
        if ($companyProfile = $form_state->getValue('field_company_profile')) {
          $this->company->set('field_company_profile', $companyProfile);
        }
        if ($companyPublic = $form_state->getValue('field_company_public_check')) {
          $this->company->set('field_company_public', $companyPublic);
        }

        break;
    }
    if (!in_array($this->company->get('field_company_type')->getString(), $this->publicSectorType)) {
      $this->company->set('field_company_email', $form_state->getValue('field_company_email'));
      $this->company->set('field_company_second_email', $form_state->getValue('field_company_second_email'));
      $this->company->set('field_company_telephone', $form_state->getValue('field_company_telephone'));
      $this->company->set('field_company_phone_extension', $form_state->getValue('field_company_telephone_extension'));
      $this->company->set('field_company_phone', $form_state->getValue('field_company_phone'));
      $this->company->set('field_company_mission_statement', $form_state->getValue('field_company_mission_statement'));
      $this->company->set('field_company_values_statement', $form_state->getValue('field_company_values_statement'));
      $addressParagraph = Paragraph::load($this->company->get('field_company_address')->getValue()[0]['target_id']);
      $addressNewValues = [
        "langcode" => "",
        "country_code" => "BR",
        'administrative_area' => $form_state->getValue('administrativeArea'),
        'locality' => $form_state->getValue('locality'),
        'dependent_locality' => $form_state->getValue('dependentLocality'),
        'address_line1' => $form_state->getValue('addressLine1'),
        'postal_code' => $form_state->getValue('postalCode'),
      ];
      $addressParagraph->set('field_paragraph_address', $addressNewValues);
      $addressParagraph->save();
    }
    $this->company->set('field_company_commitment_term', $form_state->getValue('field_company_commitment_term'));
    $moderationCurrent = $this->company->get('moderation_state')->getString();
    $commitment_term = $form_state->getValue('field_company_commitment_term', NULL);
    if ($moderationCurrent == 'draft' && $commitment_term !== NULL) {
      $this->company->set('moderation_state', 'pending_approval');

      // Set the branches to pendng approval too.
      $branchUids = \Drupal::entitytypemanager()
        ->getStorage('node')
        ->getQuery()
        ->condition('type', 'company')
        ->condition('field_company_main_office', $this->company->id())
        ->execute();
      if (is_array($branchUids) && count($branchUids)) {
        foreach ($branchUids as $branchUid) {
          $branchCompany = Node::load($branchUid);
          $branchCompany->set('moderation_state', 'pending_approval');
          $branchCompany->save();
        }
      }
    }

    $this->company->save();

    $branchUids = $this->entityTypeManager
      ->getStorage('node')
      ->getQuery()
      ->condition('type', 'company')
      ->condition('field_company_main_office', $this->company->id())
      ->execute();
    if (is_array($branchUids) && count($branchUids)) {
      foreach ($branchUids as $branchUid) {
        $branchCompany = Node::load($branchUid);
        // Set the basic data obtained from the main company to the branches.
        $branchCompany->set('field_company_logo', $this->company->get('field_company_logo')->getValue());
        $branchCompany->set('field_company_cover', $this->company->get('field_company_cover')->getValue());
        $branchCompany->set('field_company_type', $this->company->get('field_company_type')->getString());
        $branchCompany->set('field_company_start_activity', $this->company->get('field_company_start_activity')->getString());
        $branchCompany->set('field_company_branch_activity', $this->company->get('field_company_branch_activity')->getValue());
        $branchCompany->set('field_company_scope_operation', $this->company->get('field_company_scope_operation')->getValue());
        $branchCompany->set('field_company_size', $this->company->get('field_company_size')->getValue());
        $branchCompany->set('field_company_segment', $this->company->get('field_company_segment')->getValue());
        $branchCompany->set('field_company_profile', $this->company->get('field_company_profile')->getValue());
        $branchCompany->set('field_company_public', $this->company->get('field_company_public')->getValue());
        $branchCompany->set('field_company_mission_statement', $this->company->get('field_company_mission_statement')->getString());
        $branchCompany->set('field_company_values_statement', $this->company->get('field_company_values_statement')->getString());
        $branchCompany->save();
      }
    }

    $form_state->setRedirect('umio_admin_area.ta_admin.company');
  }

}
