<?php

namespace Drupal\umio_user\Form;

use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Http\RequestStack;
use Drupal\Core\Language\LanguageManager;
use Drupal\Core\Url;
use Drupal\user\Entity\User;
use Drupal\umio_helpers\Service\FormValidator;
use Drupal\password_policy\PasswordPolicyValidator;
use Drupal\umio_user\UserStatusTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Implements an example form.
 */
abstract class UserRegisterFormBase extends FormBase {

  use UserStatusTrait;

  /**
   * Define the FormValidator.
   *
   * @var \Drupal\umio_helpers\Service\FormValidator
   */
  protected $formValidator;

  /**
   * Define the LanguageManager.
   *
   * @var \Drupal\Core\Language\LanguageManager
   */
  protected $languageManager;

  /**
   * Define the entityTypeManager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * Define the passwordPolicyValidator.
   *
   * @var \Drupal\password_policy\PasswordPolicyValidator
   */
  protected $passwordPolicyValidator;

  /**
   * The request service.
   *
   * @var \Drupal\Core\Http\RequestStack
   */
  protected $requestStack;

  /**
   * Define the constructor.
   */
  final public function __construct(
    FormValidator $formValidator,
    LanguageManager $languageManager,
    EntityTypeManager $entityTypeManager,
    PasswordPolicyValidator $passwordPolicyValidator,
    RequestStack $requestStack
  ) {
    $this->formValidator = $formValidator;
    $this->languageManager = $languageManager;
    $this->entityTypeManager = $entityTypeManager;
    $this->passwordPolicyValidator = $passwordPolicyValidator;
    $this->requestStack = $requestStack;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) : FormBase {
    return new static(
      $container->get('umio_helpers.form_validator'),
      $container->get('language_manager'),
      $container->get('entity_type.manager'),
      $container->get('password_policy.validator'),
      $container->get('request_stack')
    );
  }

  /**
   * Function to return the role of the user that are going to be created.
   *
   * @return string
   *   The role of the user.
   */
  abstract protected function getRole(): string;

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form['#cache']['max-age'] = 0;
    $currentRequest = $this->requestStack->getCurrentRequest();
    $email = $currentRequest->query->get('email') ? $currentRequest->query->get('email') : NULL;
    if ($email) {
      $form_state->setValue('field_email', $email);
    }

    $form['field_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('How do you want to be called'),
      '#default_value' => $form_state->getValue('field_name'),
      '#required' => TRUE,
      '#maxlength' => 50,
    ];

    $form['field_email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email'),
      '#default_value' => $form_state->getValue('field_email'),
      '#required' => TRUE,
    ];

    $form['field_password_confirm'] = [
      '#type' => 'password_confirm',
      '#default_value' => $form_state->getValue('field_password_confirm', []),
      '#description' => $this->t('The password needs to have at least 8 characters, one number, one letter and one special character.'),
      '#size' => 25,
      '#required' => TRUE,
    ];

    $form['field_accepted_term'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Read and agreed with the') . '<a href="/termos_de_uso" target= _blank>' . $this->t('1MIO user policy') . '</a>',
      '#required' => TRUE,
    ];

    $form['actions'] = [
      '#type' => 'actions',
      'back' => [
        '#type' => 'submit',
        '#value' => $this->t('Back'),
        '#submit' => ['::pageBack'],
        '#limit_validation_errors' => [],
        '#attributes' => [
          'class' => ['btn-previous', 'btn'],
        ],
      ],
      'next' => [
        '#type' => 'submit',
        '#button_type' => 'primary',
        '#value' => $this->t('Sign up'),
        '#submit' => ['::submitForm'],
        '#validate' => ['::validateForm'],
        '#attributes' => [
          'class' => ['btn-next', 'btn', 'btn-primary'],
        ],
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    $mail = $form_state->getValue('field_email');
    if (!$this->formValidator->validateEmail($mail)) {
      $form_state->setErrorByName('field_email', $this->t('This field must be a valid e-mail.'));
    }
    else {
      if ($this->emailAlreadyUsed($mail)) {
        $form_state->setErrorByName('field_email', $this->t('This e-mail already exists.'));
      }
    }

    /*
     * We're forcing user 1 (admin), because at this step, we don't have the
     * user and the service doesn't validate against anonymous user.
     * This is a module limitation.
     */
    $validationReport = $this->passwordPolicyValidator->validatePassword($form_state->getValue('field_password_confirm'), User::load(1));
    if ($validationReport->isInvalid()) {
      $form_state->setErrorByName('field_password_confirm', $this->t('The password does not satisfy the password policies.'));
    }
  }

  /**
   * Validating if the mail is registered in the database.
   *
   * @param string|null $mail
   *   Mail to be validate.
   *
   * @return bool
   *   Boolean if the email already registered in the database.
   */
  private function emailAlreadyUsed(?string $mail): bool {
    if (!$mail) {
      return FALSE;
    }

    $uids = $this->entityTypeManager->getStorage('user')->getQuery()->condition('mail', $mail)->execute();
    if (!empty($uids)) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $data = $form_state->getValues();
    if (!empty($data)) {
      $user = User::create([
        'name' => $data['field_name'],
        'mail' => $data['field_email'],
        'pass' => $data['field_password_confirm'],
        'roles' => $this->getRole(),
      ]);

      $user->setUsername($data['field_email']);
      $user->set('field_user_name', $data['field_name']);
      $user->set('field_user_accepted_term_data', date("Y-m-d"));
      if (isset($data['field_user_birth_date'])) {
        $user->set('field_user_birth_date', $data['field_user_birth_date']);
      }
      if (isset($data['field_flag_public_sector'])) {
        $user->set('field_flag_public_sector', $data['field_flag_public_sector']);
      }

      $currentRequest = $this->requestStack->getCurrentRequest();
      $company = $currentRequest->query->get('company') ? $currentRequest->query->get('company') : NULL;
      if ($company) {
        $user->set('field_user_company', $company);
        $user->set('field_flag_singup', TRUE);
        $user->set('field_user_status', $this->getActiveStatus());
      }

      $user->enforceIsNew();

      if ($user->save()) {
        $language = $this->languageManager->getCurrentLanguage()->getId();
        // Custom notification function from user_registrationpassword module.
        _user_registrationpassword_mail_notify('register_confirmation_with_pass', $user, $language);
        $url = Url::fromRoute('umio_user.confirm_email', ['email' => $data['field_email']]);
        $form_state->setRedirectUrl($url);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function pageBack(array &$form, FormStateInterface $form_state) : void {
    $form_state->setRedirect('umio_user.register');
  }

}
