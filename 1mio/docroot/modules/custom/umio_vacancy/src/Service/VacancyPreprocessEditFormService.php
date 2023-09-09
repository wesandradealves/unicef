<?php

namespace Drupal\umio_vacancy\Service;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\file\Entity\File;
use Drupal\node\Entity\Node;
use Drupal\umio_vacancy\VacancyStatusTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class to alter vacancy edit form.
 */
class VacancyPreprocessEditFormService {

  use StringTranslationTrait;
  use VacancyStatusTrait;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  private $user;

  /**
   * The construct method.
   *
   * @param \Drupal\Core\Session\AccountInterface $user
   *   The current user.
   */
  final public function __construct(AccountInterface $user) {
    $this->user = $user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new static(
      $container->get('current_user')
    );
  }

  /**
   * Function to alter the vacancy edit form.
   *
   * @param array $form
   *   The vacancy form.
   */
  public function preprocessEditForm(array &$form): void {
    $routeParameters = \Drupal::routeMatch();
    /** @var \Drupal\node\Entity\Node $node */
    $node = $routeParameters->getParameters()->get('node');

    $this->addCompanyLogo($form, $node);

    $form['headerItems']['title'] = $node->title->getValue()[0]['value'];
    $form['headerItems']['subTitle'] = $node->field_vacancy_quantity->getValue()[0]['value'] . 'vaga(s)';

    $this->addHeaderActions($form, $node);
    $this->addFooterActions($form, $node);
  }

  /**
   * Function that add the company logo to the form.
   *
   * @param array $form
   *   The vacancy form.
   * @param \Drupal\node\Entity\Node $node
   *   The vacancy node.
   */
  private function addCompanyLogo(array &$form, Node $node): void {
    $vacancyCompany = $node->get('field_vacancy_company')->getValue();
    if ($vacancyCompany) {
      $vacancyCompanyId = $vacancyCompany[0]['target_id'];
      if ($vacancyCompanyId) {
        $vacancyCompany = Node::load($vacancyCompanyId);
        $companyLogo = $vacancyCompany->get('field_company_logo')->getValue()[0]['target_id'];
        if ($companyLogo) {
          $companyLogo = File::load($companyLogo);
          $companyLogoLink = $companyLogo->createFileUrl(FALSE);
          $form['userCompany']['imageUrl'] = $companyLogoLink;
        }
      }
    }
  }

  /**
   * Function that add the header buttons to the form.
   *
   * @param array $form
   *   The vacancy form.
   * @param \Drupal\node\Entity\Node $node
   *   The vacancy node.
   */
  private function addHeaderActions(array &$form, Node $node): void {
    $preview = $form['form']['actions']['preview'];
    unset($form['form']['actions']['preview']);
    unset($form['form']['actions']['delete']);
    $form['form']['nodeHeaderActions']['preview'] = $preview;
    $roles = $this->user->getRoles();

    if (!in_array('partner_talent_acquisition', $roles)) {
      $vacancyStatus = $this->getVacancyStatus($node);
      if ($vacancyStatus === $this->getWaitingApprovalStatus()) {
        $form['form']['nodeHeaderActions']['reject'] = [
          '#type' => 'link',
          '#title' => t('Not approved'),
          '#route_name' => 'umio_vacancy.vacancy_reject',
          '#url' => Url::fromRoute('umio_vacancy.vacancy_reject', ['nid' => $node->id()]),
          '#weight' => 100,
          '#attributes' => [
            'class' => ['use-ajax', 'btn', 'btn-danger', 'text-bold'],
            'data-dialog-type' => 'modal',
            'data-dialog-options' => Json::encode([
              'dialogClass' => 'modal-drupal-dialog',
              'width' => '500px',
            ]),
          ],
        ];
      }

      if ($vacancyStatus !== $this->getApprovalStatus() &&
          $vacancyStatus !== $this->getDeleteStatus()
      ) {
        $form['form']['nodeHeaderActions']['approve'] = [
          '#type' => 'link',
          '#title' => t('Approved'),
          '#route_name' => 'umio_vacancy.vacancy_confirm',
          '#url' => Url::fromRoute('umio_vacancy.vacancy_confirm', ['nid' => $node->id()]),
          '#weight' => 100,
          '#attributes' => [
            'class' => ['use-ajax', 'btn', 'btn-success', 'text-bold'],
            'data-dialog-type' => 'modal',
            'data-dialog-options' => Json::encode([
              'dialogClass' => 'modal-drupal-dialog',
              'width' => '500px',
            ]),
          ],
        ];
      }
    }
  }

  /**
   * Function that add the footer actions to the form.
   *
   * @param array $form
   *   The vacancy form.
   * @param \Drupal\node\Entity\Node $node
   *   The vacancy node.
   */
  private function addFooterActions(array &$form, Node $node): void {
    $statusAttributes = $this->getStatusAttributes();
    $currentStatus = $node->get('moderation_state')->getString();
    $form['nodeFooter']['nodeStatus'] = [
      'class' => $statusAttributes[$currentStatus]['Class'],
      'icon' => $statusAttributes[$currentStatus]['Icon'],
      'status' => $statusAttributes[$currentStatus]['Status'],
    ];
    $date = new \DateTime();
    $form['nodeFooter']['nodeCreated'] = [
      '#type' => 'markup',
      '#markup' => $this->t('Sent at: @date', [
        '@date' => $date->setTimestamp($node->created->getValue()[0]['value'])->format('d/m/Y'),
      ]),
    ];

    $vacancyStatus = $this->getVacancyStatus($node);
    if ($vacancyStatus !== $this->getDeleteStatus()) {
      $form['nodeFooter']['delete'] = [
        '#type' => 'link',
        '#title' => $this->t('Delete vacancy'),
        '#url' => Url::fromRoute('umio_vacancy.vacancy_delete', ['nid' => $node->id()]),
        '#weight' => 100,
        '#attributes' => [
          'class' => ['use-ajax', 'text-bold', 'btn-delete'],
          'data-dialog-type' => 'modal',
          'data-dialog-options' => Json::encode([
            'dialogClass' => 'modal-drupal-dialog',
          ]),
        ],
      ];
    }
  }

  /**
   * Function to return attributes of the status.
   *
   * @return array
   *   Array with the attributes of the status.
   */
  private function getStatusAttributes(): array {
    return [
      'published' => [
        'Icon' => 'ph-check',
        'Class' => 'text-success',
        'Status' => $this->t('Active'),
      ],
      'not_approved' => [
        'Icon' => 'ph-shield-warning',
        'Class' => 'text-danger',
        'Status' => $this->t('Not approved'),
      ],
      'draft' => [
        'Icon' => 'ph-clock',
        'Class' => 'text-warning',
        'Status' => $this->t('Waiting approve'),
      ],
      'canceled' => [
        'Icon' => 'ph-trash',
        'Class' => 'text-danger',
        'Status' => $this->t('Deleted'),
      ],
    ];
  }

}
