<?php

namespace Drupal\company\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Abstract class to change status of company content type.
 */
abstract class AbstractStatusForm extends ConfirmFormBase {

  /**
   * Company node.
   *
   * @var \Drupal\node\NodeInterface|null
   */
  protected $companyNode;

  /**
   * Node ID of the company.
   *
   * @var string|null
   */
  protected $nid;

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
   * Send email with the justify to the blocked company.
   */
  public function sendCompanyBlockJustify(string $justify): void {
    $langcode = \Drupal::currentUser()->getPreferredLangcode();

    $companyName = $this->companyNode->get('title')->getString();
    $systemEmail = \Drupal::config('system.site')->get('mail');

    $params['context']['subject'] = $this->t('1MiO - Your registration request needs review.');
    $params['context']['message'] = $this->t("@name,\n\nYour access to 1MiO was blocked for the following reason:\n\n@justify\n\nCheck your details and contact us by email: @systemEmail\n\n", [
      '@name' => $companyName,
      '@justify' => $justify,
      '@systemEmail' => $systemEmail,
    ]);
    $to = $this->companyNode->get('field_company_email')->getString();
    if (!empty($to)) {
      $this->mail->mail('system', 'mail', $to, $langcode, $params);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, string $nid = NULL) {
    $this->nid = $nid;
    /** @var \Drupal\node\NodeInterface|null $companyNode */
    $companyNode = \Drupal::entityTypeManager()->getStorage('node')->load($this->nid);
    $this->companyNode = $companyNode;
    $form = parent::buildForm($form, $form_state);

    return $form;
  }

  /**
   * Get the company type field.
   *
   * @return string
   *   The company type field value.
   */
  public function getCompanyType(): string {
    return $this->companyNode->get('field_company_type')->getString();
  }

}
