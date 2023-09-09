<?php

namespace Drupal\umio_user\Form\PartnerTalentAcquisition;

use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Abstract class to change status of company content type.
 */
abstract class AbstractStatusTAForm extends ConfirmFormBase {

  /**
   * Define the constructor.
   */
  final public function __construct(MailManagerInterface $mail) {
    $this->mail = $mail;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) : self {
    return new static(
      $container->get('plugin.manager.mail'),
    );
  }

  /**
   * Mail service.
   *
   * @var \Drupal\Core\Mail\MailManagerInterface|null
   */
  protected $mail;

  /**
   * User entity.
   *
   * @var \Drupal\user\Entity\User
   */
  protected $user;

  /**
   * Node ID of the company.
   *
   * @var string|null
   */
  protected $uid;

  /**
   * Send email with the justify to the blocked user.
   */
  public function sendUserBlockJustify(string $justify): void {
    $langcode = \Drupal::currentUser()->getPreferredLangcode();

    $userName = $this->user->get('name')->getString();
    $systemEmail = \Drupal::config('system.site')->get('mail');

    $params['context']['subject'] = $this->t('1MiO - Your registration request needs review.');
    $params['context']['message'] = $this->t("@name,\n\nYour access to 1MiO was blocked for the following reason:\n\n@justify\n\nCheck your details and contact us by email: @systemEmail\n\n", [
      '@name' => $userName,
      '@justify' => $justify,
      '@systemEmail' => $systemEmail,
    ]);

    $to = $this->user->get('mail')->getString();
    if (!empty($to)) {
      $this->mail->mail('system', 'mail', $to, $langcode, $params);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, string $uid = NULL) {
    $this->uid = $uid;

    /** @var \Drupal\user\Entity\User|null $user */
    $user = \Drupal::entityTypeManager()->getStorage('user')->load($this->uid);
    if ($user) {
      $this->user = $user;
    }
    $form = parent::buildForm($form, $form_state);

    return $form;
  }

}
