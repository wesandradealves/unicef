<?php

namespace Drupal\company\Service;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\umio_helpers\Service\FormValidator;

/**
 * Class to validate the company form.
 */
final class CompanyFormValidator {

  /**
   * The token manager.
   *
   * @var \Drupal\umio_helpers\Service\FormValidator
   */
  public $formValidator;

  /**
   * The current path request.
   *
   * @var \Drupal\Core\Path\CurrentPathStack
   */
  protected $currentPath;

  /**
   * Constructs a \Drupal\company\Service\CompanyFormValidator object.
   *
   * @param \Drupal\umio_helpers\Service\FormValidator $formValidator
   *   Helper class to validate input.
   * @param \Drupal\Core\Path\CurrentPathStack $currentPath
   *   The current path request.
   */
  public function __construct(FormValidator $formValidator, CurrentPathStack $currentPath) {
    $this->formValidator = $formValidator;
    $this->currentPath = $currentPath;
  }

  /**
   * Function to validate fields for content type Company.
   *
   * @var \Drupal\Core\Form\FormStateInterface $form_state
   */
  public function validate(FormStateInterface $form_state): void {
    $company_type = $form_state->getValue('field_company_type')[0]["value"];
    $this->validateCompanyType($company_type, $form_state);
    if ($company_type == 'company') {
      $company_cnpj = $form_state->getValue('field_company_cnpj')[0]["value"];
      $this->validateCnpj($company_cnpj, $form_state);
    }
    else {
      $company_start_activity = $form_state->getValue('field_company_start_activity')[0]["value"];
      $this->validateStartActivity($company_start_activity, $form_state);
    }
    $company_email = $form_state->getValue('field_company_email')[0]["value"];
    $this->validateEmail($company_email, $form_state);
    $company_second_email = $form_state->getValue('field_company_second_email')[0]["value"];
    $this->validateSecondEmail($company_second_email, $form_state);
    $company_telephone = $form_state->getValue('field_company_telephone')[0]["value"];
    $this->validateTelephone($company_telephone, $form_state);
    $company_telephone_extension = $form_state->getValue('field_company_phone_extension')[0]["value"];
    $this->validateTelephoneExtension($company_telephone_extension, $form_state);
    $company_phone = $form_state->getValue('field_company_phone')[0]["value"];
    $this->validatePhone($company_phone, $form_state);
  }

  /**
   * Function to validate fields for content type Company on branch creation.
   *
   * @var \Drupal\Core\Form\FormStateInterface $form_state
   */
  public function validateForm(FormStateInterface $form_state): void {
    $company_type = $form_state->getValue('field_company_type');
    $this->validateCompanyType($company_type, $form_state);
    if ($company_type == 'company') {
      $company_cnpj = $form_state->getValue('field_company_cnpj');
      $this->validateCnpj($company_cnpj, $form_state);
    }
    else {
      $company_start_activity = $form_state->getValue('field_company_start_activity');
      $this->validateStartActivity($company_start_activity, $form_state);
    }
    $company_email = $form_state->getValue('field_company_email');
    $this->validateEmail($company_email, $form_state);
    $company_second_email = $form_state->getValue('field_company_second_email');
    $this->validateSecondEmail($company_second_email, $form_state);
    $company_telephone = $form_state->getValue('field_company_telephone');
    $this->validateTelephone($company_telephone, $form_state);
    $company_telephone_extension = $form_state->getValue('field_company_phone_extension');
    $this->validateTelephoneExtension($company_telephone_extension, $form_state);
    $company_phone = $form_state->getValue('field_company_phone');
    $this->validatePhone($company_phone, $form_state);
  }

  /**
   * Function to validate field_company_type field for Company.
   *
   * @var \Drupal\Core\Form\FormStateInterface $form_state
   */
  private function validateCompanyType(?string $company_type, FormStateInterface $form_state): void {
    switch ($company_type) {
      case 'company':
      case 'civil-society':
      case 'city':
        break;

      default:
        $form_state->setErrorByName('field_company_type', t('This field must be either a company, city or a civil society.'));
    }
  }

  /**
   * Function to validate field_company_cnpj field field for Company.
   *
   * @var \Drupal\Core\Form\FormStateInterface $form_state
   */
  public function validateCnpj(?string $company_cnpj, FormStateInterface $form_state): void {
    if ($company_cnpj && !$this->formValidator->validateCnpj($company_cnpj)) {
      $form_state->setErrorByName('field_company_cnpj', t('This field must be a valid CNPJ.'));
    }
    if ($company_cnpj && $this->isCnpjAlreadyUsed($company_cnpj)) {
      $form_state->setErrorByName('field_company_cnpj', t('Duplicate CNPJ are not allowed.'));
    }
  }

  /**
   * Function to check if CNPJ is already used in database.
   *
   * @var ?string $cnpj
   */
  private function isCnpjAlreadyUsed(?string $cnpj): bool {
    if ($cnpj) {
      $currentPath = $this->currentPath->getPath();
      $node_id = explode('/', $currentPath)[2];
      /** @var array */
      $entity_id = \Drupal::entityQuery('node')->condition('field_company_cnpj', $cnpj)->condition('type', 'company')->execute();
      return !empty($entity_id) && !in_array($node_id, $entity_id);
    }

    return FALSE;
  }

  /**
   * Function to validate field_company_start_activity field field for Company.
   *
   * @var \Drupal\Core\Form\FormStateInterface $form_state
   */
  public function validateStartActivity(?string $company_start_activity, FormStateInterface $form_state): void {
    if ($company_start_activity && !strtotime($company_start_activity)) {
      $form_state->setErrorByName('field_company_start_activity', t('This field must be a valid date.'));
    }
  }

  /**
   * Function to validate field_company_email field for Company.
   *
   * @var \Drupal\Core\Form\FormStateInterface $form_state
   */
  public function validateEmail(?string $company_email, FormStateInterface $form_state): void {
    if ($company_email && !$this->formValidator->validateEmail($company_email)) {
      $form_state->setErrorByName('field_company_email', t('Invalid Email.'));
    }
    if ($company_email && $this->isEmailAlreadyUsed($company_email)) {
      $form_state->setErrorByName('field_company_email', t('Email already in use.'));
    }
  }

  /**
   * Function to validate field_company_second_email field for Company.
   *
   * @var \Drupal\Core\Form\FormStateInterface $form_state
   */
  public function validateSecondEmail(?string $company_email, FormStateInterface $form_state): void {
    // Validating second email.
    if ($company_email && !$this->formValidator->validateEmail($company_email)) {
      $form_state->setErrorByName('field_company_second_email', t('Invalid Email.'));
    }
  }

  /**
   * Function to check if email is already used in database.
   *
   * @var ?string $email
   */
  private function isEmailAlreadyUsed(?string $email): bool {
    if ($email) {
      $currentPath = $this->currentPath->getPath();
      $node_id = explode('/', $currentPath)[2];
      /** @var array */
      $entity_id = \Drupal::entityQuery('node')->condition('field_company_email', $email)->condition('type', 'company')->execute();
      return !empty($entity_id) && !in_array($node_id, $entity_id);
    }

    return FALSE;
  }

  /**
   * Function to validate field_company_telephone field field for Company.
   *
   * @var \Drupal\Core\Form\FormStateInterface $form_state
   */
  public function validateTelephone(?string $company_telephone, FormStateInterface $form_state): void {
    if ($company_telephone && !$this->formValidator->validateTelephone($company_telephone)) {
      $form_state->setErrorByName('field_company_telephone', t('This field must be a valid Telephone.'));
    }
  }

  /**
   * Function to validate field_company_phone_extension field field for Company.
   *
   * @var \Drupal\Core\Form\FormStateInterface $form_state
   */
  public function validateTelephoneExtension(?string $company_telephone_extension, FormStateInterface $form_state): void {
    if ($company_telephone_extension && !$this->formValidator->validateTelephoneExtension($company_telephone_extension)) {
      $form_state->setErrorByName('field_company_telephone_extension', t('This field must be a valid Telephone Extension.'));
    }
  }

  /**
   * Function to validate field_company_phone field field for Company.
   *
   * @var \Drupal\Core\Form\FormStateInterface $form_state
   */
  public function validatePhone(?string $company_phone, FormStateInterface $form_state): void {
    if ($company_phone && !$this->formValidator->validatePhone($company_phone)) {
      $form_state->setErrorByName('field_company_phone', t('This field must be a valid Phone.'));
    }
  }

}
