<?php

namespace Drupal\company\Form\City;

use Drupal\company\Service\CityFieldsService;
use Drupal\company\Service\CompanyFieldsService;
use Drupal\company\Service\CompanyWorkflowService;
use Drupal\company_manager\Service\TalentAcquisitionFields;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
use Drupal\node\NodeInterface;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\umio_batch_import\Form\CityStamp\CityStampConstInterface;
use Drupal\umio_helpers\Service\AddressService;
use Drupal\user\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class CityEditForm to consultancy user.
 */
class CityEditForm extends FormBase {

  /**
   * The city fields service.
   *
   * @var \Drupal\company\Service\CityFieldsService
   */
  protected $cityFieldsService;

  /**
   * Define the companyFieldsService.
   *
   * @var \Drupal\company\Service\CompanyFieldsService
   */
  protected $companyFieldsService;

  /**
   * Define the companyWorkflowService.
   *
   * @var \Drupal\company\Service\CompanyWorkflowService
   */
  protected $companyWorkflowService;

  /**
   * Define the entityTypeManager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Service to get talent acquisition user fields.
   *
   * @var \Drupal\company_manager\Service\TalentAcquisitionFields
   */
  protected $talentAcquisitionFieldsService;

  /**
   * City node.
   *
   * @var \Drupal\node\NodeInterface
   */
  protected $node;

  /**
   * User that creates the city node.
   *
   * @var \Drupal\user\Entity\User
   */
  protected $user;

  /**
   * Define the constructor.
   *
   * @param \Drupal\company\Service\CityFieldsService $cityFieldsService
   *   Service to get company fields.
   * @param \Drupal\company\Service\CompanyFieldsService $companyFieldsService
   *   Define the companyFieldsService.
   * @param \Drupal\company\Service\CompanyWorkflowService $companyWorkflowService
   *   Define the companyWorkflowService.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\company_manager\Service\TalentAcquisitionFields $talentAcquisitionFields
   *   Service to get talent acquisition user fields.
   */
  final public function __construct(
    CityFieldsService $cityFieldsService,
    CompanyFieldsService $companyFieldsService,
    CompanyWorkflowService $companyWorkflowService,
    EntityTypeManagerInterface $entityTypeManager,
    TalentAcquisitionFields $talentAcquisitionFields
  ) {
    $this->cityFieldsService = $cityFieldsService;
    $this->companyFieldsService = $companyFieldsService;
    $this->companyWorkflowService = $companyWorkflowService;
    $this->entityTypeManager = $entityTypeManager;
    $this->talentAcquisitionFieldsService = $talentAcquisitionFields;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new static(
      $container->get('company.city_fields_service'),
      $container->get('company.company_fields'),
      $container->get('company.company_workflow_service'),
      $container->get('entity_type.manager'),
      $container->get('company_manager.talent_acquisition_fields'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'city_edit_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, NodeInterface $node = NULL): array {
    $form['#attributes']['enctype'] = 'multipart/form-data';
    if ($node) {
      $this->node = $node;
      $this->user = $this->getTheUserFromCityNode($node);

      $form = $this->getFormMarkup($node, $form);

      $form['user_markup'] = [
        '#type' => 'markup',
        '#markup' => '<h4>Dados do TA</h4>',
      ];
      $form = $this->talentAcquisitionFieldsService->createTalentAcquisitionFields($form, $form_state);
      $form['field_company_role']['#prefix'] = '';
      $form['field_company_role']['#suffix'] = '';
      unset($form['field_company_role_started']);
      unset($form['field_address']);
      $addressParagraph = Paragraph::load($this->user->get('field_user_address')->getValue()[0]['target_id']);
      if ($addressParagraph) {
        $address = $addressParagraph->get('field_paragraph_address')->getValue()[0];
        $form['field_address'] = AddressService::getStateAndLocalityField($address['administrative_area'], $address['locality']);
      }

      $form['city_markup'] = [
        '#type' => 'markup',
        '#markup' => '<h4>Dados da Empresa</h4>',
      ];
      $form = $this->cityFieldsService->createCityFields($form, $form_state);
      unset($form['field_company_logo']);
      $form = $this->fillUserFormData($form);
      $form = $this->fillCityFormData($form);
      $form = $this->disabledFields($form);

      unset($form['field_download_commitment_term_infos']);
      unset($form['field_company_commitment_term_infos']);
      unset($form['field_company_commitment_term']);

      $form = $this->handleFieldsUnicefStamp($form);

      unset($form['actions']);

      $form = $this->handleOrder($form);
    }

    $form['#theme'] = 'form_workflow';

    return $form;
  }

  /**
   * Function to get the user from city node.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The company node.
   *
   * @return \Drupal\user\Entity\User
   *   The user that creates the city.
   */
  private function getTheUserFromCityNode(NodeInterface $node): User {
    $user = $this->entityTypeManager->getStorage('user')->getQuery()->condition('field_user_company', $node->id())->execute();
    if (!$user) {
      throw new NotFoundHttpException();
    }
    $user = User::load(array_key_first($user));
    if (!$user) {
      throw new NotFoundHttpException();
    }

    return $user;
  }

  /**
   * Fill the fields for the talent acquisition user.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The city node.
   * @param array $form
   *   Define the form variable array.
   *
   * @return array
   *   The form filled with talent acquisition personal data.
   */
  private function getFormMarkup(NodeInterface $node, array $form) {
    $logo = $node->get('field_company_logo')->getValue();
    if ($logo) {
      $file = File::load($logo[0]['target_id']);
      if ($file) {
        $url = $file->createFileUrl(TRUE);
        $form['company_logo'] = [
          '#type' => 'markup',
          '#markup' => "<img src='$url'>",
        ];
      }
    }

    $name = $node->get('title')->getString();
    $addressParagraph = Paragraph::load($this->node->get('field_company_address')->getValue()[0]['target_id']);
    if ($addressParagraph) {
      $address = $addressParagraph->get('field_paragraph_address')->getValue()[0];
      $administrativeArea = $address['administrative_area'];
      $form['description'] = [
        '#type' => 'markup',
        '#markup' => "<h3>$name - $administrativeArea</h3>",
      ];
    }

    $form['header_actions'] = $this->companyWorkflowService->getApproveAndRejectWorkflowFields($node);
    if (isset($form['header_actions']['reject'])) {
      $form['header_actions']['reject']['#title'] = $this->t('Disapprove');
    }

    $form['footer_actions'] = $this->companyWorkflowService->getModerationStateAndCreatedAtFields($node);
    $form['footer_actions']['delete_action'] = $this->companyWorkflowService->getDeleteWorkflowField($node);

    return $form;
  }

  /**
   * Disabled all fields inside the form.
   *
   * @param array $form
   *   Define the form variable array.
   *
   * @return array
   *   The form filled with all fields disabled.
   */
  private function disabledFields(array $form): array {
    foreach ($form as $field => $value) {
      if ($field === 'header_actions') {
        continue;
      }
      $form[$field]['#disabled'] = 'disabled';
    }

    return $form;
  }

  /**
   * Fill the fields for the talent acquisition user.
   *
   * @param array $form
   *   Define the form variable array.
   *
   * @return array
   *   The form filled with talent acquisition personal data.
   */
  private function fillUserFormData(array $form): array {
    $form['field_name']['#default_value'] = $this->user->get('field_user_name')->getString();
    $form['field_email']['#default_value'] = $this->user->get('mail')->getString();
    $form['field_company_role']['#default_value'] = $this->user->get('field_user_company_role')->getString();
    $form['field_telephone']['#default_value'] = $this->user->get('field_user_telephone')->getString();
    $form['field_telephone_extension']['#default_value'] = $this->user->get('field_user_telephone_extension')->getString();
    $form['field_phone']['#default_value'] = $this->user->get('field_user_phone')->getString();

    return $form;
  }

  /**
   * Fill the fields for the city node.
   *
   * @param array $form
   *   Define the form variable array.
   *
   * @return array
   *   The form filled with company data.
   */
  private function fillCityFormData(array $form): array {
    $type = $this->node->get('field_company_type')->getString();
    $form['field_company_cover']['#default_value'] = explode(', ', $this->node->get('field_company_cover')->getString());
    $form['field_company_unicef_stamp']['#default_value'] = $this->node->get('field_company_unicef_stamp')->getString();
    $form['field_company_region']['#default_value'] = $this->node->get('field_company_region')->getString();
    $form['field_institution_type']['#default_value'] = $type;

    $addressParagraph = Paragraph::load($this->node->get('field_company_address')->getValue()[0]['target_id']);
    if ($addressParagraph) {
      $address = $addressParagraph->get('field_paragraph_address')->getValue()[0];

      $form['field_company_address']['companyAdministrativeArea']['#default_value'] = $address['administrative_area'];
      if ($type === 'state') {
        $prefix = str_replace('col-4', '', $form['field_company_address']['companyAdministrativeArea']['#prefix']);
        $form['field_company_address']['companyAdministrativeArea']['#prefix'] = $prefix;
      }
      $form['field_company_address']['companyLocality']['#options'] = [$address['locality']];
      $form['field_company_address']['companyLocality']['#default_value'] = $address['locality'];
      $form['field_company_address']['companyAdministrativeArea']['#options'] = [
        $address['administrative_area'] => CityStampConstInterface::LIST_STATES[$address['administrative_area']],
      ];
    }

    return $form;
  }

  /**
   * Add or remove fields based on field_company_unicef_stamp field.
   *
   * @param array $form
   *   Define the form variable array.
   *
   * @return array
   *   The form filled correct data if is unicef stamp or not.
   */
  private function handleFieldsUnicefStamp(array $form): array {
    $isUnicefStamp = $this->node->get('field_company_unicef_stamp')->getString();
    $form['field_company_address']['companyAdministrativeArea']['#title'] = $this->t('UF');
    if ($isUnicefStamp) {
      $form['field_company_unicef_stamp']['#type'] = 'hidden';
      $commitmentTerm = $this->node->get('field_company_commitment_term')->getString();
    }
    else {
      unset($form['field_company_unicef_stamp']);
      unset($form['field_company_region']);
      $commitmentTerm = $this->node->get('field_company_commitment_term')->getValue();
      if ($commitmentTerm) {
        $file = File::load($commitmentTerm[0]['target_id']);
        if ($file) {
          $url = $file->createFileUrl();
          $form['commitment_term_link'] = [
            '#type' => 'markup',
            '#markup' => '<div class="term-of-commitment-link"><a href=' . $url . ' target=_blank>' . $this->t('See term of commitment') . '</a></div>',
          ];
        }
      }
      else {
        $form['commitment_term_link'] = [
          '#type' => 'markup',
          '#markup' => '<div class="term-of-commitment-link"><p>' . $this->t('Awaiting submission of the term of commitment') . '</p></div>',
        ];
      }
    }

    return $form;
  }

  /**
   * Change the order of fields in the form.
   *
   * @param array $form
   *   Define the form variable array.
   *
   * @return array
   *   The form with fields ordered.
   */
  private function handleOrder(array $form): array {
    $form['header_actions']['#weight'] = 1;
    // User.
    $form['user_markup']['#weight'] = 2;
    $form['field_name']['#weight'] = 3;
    $form['field_email']['#weight'] = 4;
    $form['field_telephone']['#weight'] = 5;
    $form['field_telephone_extension']['#weight'] = 6;
    $form['field_phone']['#weight'] = 7;
    $form['field_address']['#weight'] = 8;
    $form['field_company_role']['#weight'] = 9;

    // City.
    $form['city_markup']['#weight'] = 10;
    $form['field_company_cover']['#weight'] = 11;
    if (isset($form['field_company_unicef_stamp'])) {
      $form['field_company_unicef_stamp']['#weight'] = 12;
      $form['field_company_region']['#weight'] = 13;
      $form['field_institution_type']['#weight'] = 14;
      $form['field_company_address']['#weight'] = 15;
    }
    else {
      $form['field_institution_type']['#weight'] = 16;
      $form['field_company_address']['#weight'] = 17;
      $form['commitment_term_link']['#weight'] = 18;
    }

    $form['footer_actions']['#weight'] = 0;

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
  }

}
