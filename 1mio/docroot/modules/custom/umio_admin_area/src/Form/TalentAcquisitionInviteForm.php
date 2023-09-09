<?php

namespace Drupal\umio_admin_area\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Mail\MailManager;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 1mio Admin Area form.
 */
class TalentAcquisitionInviteForm extends FormBase {

  /**
   * The account interface.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $user;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The Mail Service.
   *
   * @var \Drupal\Core\Mail\MailManager
   */
  protected $mailManager;

  /**
   * Constructs the Talent Acquisition form.
   *
   * @param \Drupal\Core\Session\AccountInterface $user
   *   The account interface.
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   *   The language manager.
   * @param \Drupal\Core\Mail\MailManager $mailManager
   *   The mail managegr service.
   */
  final public function __construct(
    AccountInterface $user,
    LanguageManagerInterface $languageManager,
    MailManager $mailManager
  ) {
    $this->user = $user;
    $this->languageManager = $languageManager;
    $this->mailManager = $mailManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new static(
      $container->get('current_user'),
      $container->get('language_manager'),
      $container->get('plugin.manager.mail'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'umio_admin_area_talent_acquisition_invite';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Insert the e-mail:'),
      '#required' => TRUE,
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Send'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state):void {
    $currentUser = User::load($this->user->id());
    $companyId = $currentUser->get('field_user_company')->getString();
    $company = Node::load($companyId);
    $companyTitle = $company->getTitle();
    $inviteEmail = (string) $form_state->getValue('email');
    $routeName = in_array('partner_talent_acquisition', $currentUser->getRoles()) ? 'company_manager.register' : 'company_manager.register_city_manager';
    $emailUrl = Url::fromRoute($routeName, [], [
      'query' => [
        'company' => $companyId,
        'email' => $inviteEmail,
      ],
    ]);
    $this->sendEmail($emailUrl, $inviteEmail, $companyTitle);
    $this->messenger()->addStatus($this->t('The e-mail has been sent'));
    $form_state->setRedirect('umio_admin_area.ta_admin.company_people');
  }

  /**
   * Send e-mail.
   *
   * @param \Drupal\Core\Url $emailUrl
   *   The url from e-mail body.
   * @param string $inviteEmail
   *   The email subject.
   * @param string $companyTitle
   *   The company name.
   */
  private function sendEmail(Url $emailUrl, string $inviteEmail, string $companyTitle): void {
    /** @var \Drupal\Core\Language\Language $language */
    $language = $this->languageManager->getCurrentLanguage();
    $link = $emailUrl;
    $link->setAbsolute();
    $link = $link->toString();
    $linkLogin = Url::fromRoute('user.login');
    $linkLogin->setAbsolute();
    $linkLogin = $linkLogin->toString();
    $this->mailManager->mail('umio_admin_area', 'invite_email', $inviteEmail, $language->getId(), [
      'subject' => $this->t("You're invite to join in 1mio platform"),
      'link' => $link,
      'link_login' => $linkLogin,
      'email' => $inviteEmail,
      'company_name' => $companyTitle,
    ]);
  }

}
