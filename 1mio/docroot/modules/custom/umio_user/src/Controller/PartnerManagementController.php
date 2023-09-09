<?php

namespace Drupal\umio_user\Controller;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;
use Drupal\file\Entity\File;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\umio_user\UserStatusTrait;

/**
 * Class PartnerManagementController that returns the data for the TA.
 */
class PartnerManagementController extends ControllerBase {

  use UserStatusTrait;

  /**
   * Define the status icons array.
   *
   * @var array
   */
  public $statusIcon = [
    'Ativo' => [
      'Icon' => '',
      'Class' => 'text-success',
    ],
    'Bloqueado' => [
      'Icon' => 'ph-shield-warning',
      'Class' => 'text-danger',
    ],
    'Aguardando Ativação' => [
      'Icon' => 'ph-clock',
      'Class' => 'text-warning',
    ],
  ];

  /**
   * Renders the people profile.
   *
   * @return array
   *   Returns a array the render information.
   */
  public function renderProfile(int $uid): array {
    $target_user = User::load($uid);
    $target_user_company_id = $target_user->field_user_company->getValue();
    $target_user_company_id = $target_user_company_id[0]['target_id'];

    $current_user = User::load(\Drupal::currentUser()->id());
    $company_user = Node::load($target_user_company_id);
    $date = new \DateTime();

    $output = [
      '#title' => $this->t('People from company'),
      '#theme' => 'page_admin_people_profile',

      '#company_user' => $company_user,
      '#current_user' => $current_user,
      '#target_user' => $target_user,

      '#headerActions' => [],
      '#bodyItems' => [],
    ];

    if ($current_user->id() != $uid) {
      $output['#headerActions'] = $this->generateHeaderActions($target_user);
    }

    $company_logo = $company_user->get('field_company_logo')->getString();
    $company_logo = File::load($company_logo);
    if ($company_logo) {
      $company_logo_link = $company_logo->createFileUrl(FALSE);
      $output['#bodyItems']['logo'] = $company_logo_link;
    }

    $user_current_status = $this->validateField($target_user->field_user_status) ? $target_user->field_user_status->getValue()[0]['value'] : 'Aguardando Ativação';

    $output['#bodyItems']['current_status'] = [
      'status' => $user_current_status,
      'icon' => $this->statusIcon[$user_current_status]['Icon'],
      'class' => $this->statusIcon[$user_current_status]['Class'],
    ];

    $output['#bodyItems']['created_date'] = "Enviado em " . $date->setTimestamp($target_user->created->getValue()[0]['value'])->format('d/m/Y');

    $output['#bodyItems']['form'] = $this->generateBodyForm($target_user);

    return $output;

  }

  /**
   * Define the generateHeaderActions.
   *
   * @param \Drupal\user\Entity\User $user
   *   The target user.
   *
   * @return array
   *   Return a array with the form buttons.
   */
  private function generateHeaderActions(User $user) : array {
    $commonClasses = [
      'use-ajax',
      'btn',
      'text-bold',
      'd-flex',
      'justify-content-center',
      'align-items-center',
      'header-btn-sizes',
    ];

    $header_actions = [];

    $status = $this->getUserStatus($user);
    if ($status === $this->getWaitingApprovalStatus()) {
      $header_actions['reject'] = [
        '#type' => 'link',
        '#title' => 'Bloquear',
        '#url' => Url::fromRoute('umio_user.reject', ['uid' => $user->id()]),
        "#weight" => 100,
        '#attributes' => [
          'class' => array_merge($commonClasses, ['btn-danger']),
          'data-dialog-type' => 'modal',
          'data-dialog-options' => Json::encode([
            'width' => '380',
            'dialogClass' => 'modal-drupal-dialog',
          ]),
        ],
      ];
    }

    if ($status !== $this->getActiveStatus()) {
      $header_actions['approve'] = [
        '#type' => 'link',
        '#title' => 'Aprovar',
        '#url' => Url::fromRoute('umio_user.approve', ['uid' => $user->id()]),
        "#weight" => 100,
        '#attributes' => [
          'class' => array_merge($commonClasses, ['btn-success']),
          'data-dialog-type' => 'modal',
          'data-dialog-options' => Json::encode([
            'width' => '380',
            'dialogClass' => 'modal-drupal-dialog',
          ]),
        ],
      ];
    }

    return $header_actions;

  }

  /**
   * Validate if the field is defined and has value.
   *
   * @param null|\Drupal\Core\Field\FieldItemListInterface $currentField
   *   Define the currentField parameter that recieve the field to be verified.
   *
   * @return bool
   *   Return a boolean value with the result.
   */
  private function validateField(?FieldItemListInterface $currentField): bool {
    if ($currentField !== NULL && $currentField->getValue()) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Define the generateBodyFunction.
   *
   * @param \Drupal\user\Entity\User $target_user
   *   Define the target_user varaible.
   *
   * @return array
   *   Return a array a form filled with user data.
   */
  private function generateBodyForm(User $target_user): array {

    $body_form = [];

    $body_form['field_email'] = [
      '#type' => 'email',
      '#title' => $this->t('Main Email'),
      '#value' => $this->validateField($target_user->mail) ? $target_user->mail->getValue()[0]['value'] : '',
      '#attributes' => [
        'readonly' => TRUE,
      ],
      '#required' => TRUE,
    ];

    $body_form['field_telephone'] = [
      '#prefix' => '<div class="d-flex justify-content-between">',
      '#wrapper_attributes' => [
        'class' => [
          'form-group-item-big',
        ],
      ],
      '#type' => 'textfield',
      '#title' => $this->t('Landline'),
      '#value' => $this->validateField($target_user->field_user_telephone) ? $target_user->field_user_telephone->getValue()[0]['value'] : '',
      '#attributes' => [
        'readonly' => TRUE,
      ],
      '#required' => TRUE,
    ];

    $body_form['field_telephone_extension'] = [
      '#suffix' => '</div>',
      '#wrapper_attributes' => [
        'class' => [
          'form-group-item-small',
        ],
      ],
      '#type' => 'textfield',
      '#title' => $this->t('Phone extension'),
      '#value' => $this->validateField($target_user->field_user_phone_extension) ? $target_user->field_user_phone_extension->getValue()[0]['value'] : '',
      '#attributes' => [
        'readonly' => TRUE,
      ],
    ];

    $body_form['field_phone'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Cell Phone'),
      '#value' => $this->validateField($target_user->field_user_phone) ? $target_user->field_user_phone->getValue()[0]['value'] : '',
      '#attributes' => [
        'readonly' => TRUE,
      ],
    ];

    $administrative_area = '';
    $locality = '';
    $address = $target_user->field_user_address->getValue();
    if (isset($address[0])) {
      $target_id_user_address = $address[0]['target_id'] ? $address[0]['target_id'] : NULL;
      $paragraph = Paragraph::load($target_id_user_address);
      if ($paragraph) {
        $paragraph_address = $paragraph->field_paragraph_address->getValue();
        $administrative_area = $paragraph_address ? $paragraph_address[0]['administrative_area'] : '';
        $locality = $paragraph_address ? $paragraph_address[0]['locality'] : '';
      }
    }

    $body_form['field_address']['administrativeArea'] = [
      '#prefix' => '<div class="d-flex justify-content-between">',
      '#wrapper_attributes' => [
        'class' => [
          'form-group-item-small',
        ],
      ],
      '#type' => 'textfield',
      '#title' => $this->t('Administrative area'),
      '#value' => $administrative_area,
      '#attributes' => [
        'readonly' => TRUE,
      ],
      '#required' => TRUE,
    ];

    $body_form['field_address']['locality'] = [
      '#suffix' => '</div>',
      '#wrapper_attributes' => [
        'class' => [
          'form-group-item-big',
        ],
      ],
      '#type' => 'textfield',
      '#title' => $this->t('City'),
      '#value' => $locality,
      '#attributes' => [
        'readonly' => TRUE,
      ],
      '#required' => TRUE,
    ];

    $body_form['field_user_company_role'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Role on company'),
      '#value' => $this->validateField($target_user->field_user_company_role) ? $target_user->field_user_company_role->getValue()[0]['value'] : '',
      '#attributes' => [
        'readonly' => TRUE,
      ],
    ];

    return $body_form;
  }

}
