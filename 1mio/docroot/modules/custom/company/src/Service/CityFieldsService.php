<?php

namespace Drupal\company\Service;

use Drupal\address\Repository\SubdivisionRepository;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Implements the construction fields of the Talent Acquisition in form.
 */
class CityFieldsService {

  use DependencySerializationTrait;
  use StringTranslationTrait;

  /**
   * Define the subdivisionRepository.
   *
   * @var \Drupal\address\Repository\SubdivisionRepository
   */
  private $subdivisionRepository;

  /**
   * Define the entityTypeManager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  private $entityTypeManager;

  /**
   * Define the companyFieldsService.
   *
   * @var \Drupal\company\Service\CompanyFieldsService
   */
  protected $companyFieldsService;

  /**
   * Define the cityStampService.
   *
   * @var \Drupal\company\Service\CityStampService
   */
  protected $cityStampService;

  /**
   * Define the constructor.
   *
   * @param \Drupal\address\Repository\SubdivisionRepository $subdivisionRepository
   *   Define the subdivisionRepository.
   * @param \Drupal\Core\Entity\EntityTypeManager $entityTypeManager
   *   Define the entityTypeManager.
   * @param \Drupal\company\Service\CompanyFieldsService $companyFieldsService
   *   Define the companyFieldsService.
   * @param \Drupal\company\Service\CityStampService $cityStampService
   *   Define the cityStampService.
   */
  final public function __construct(
   SubdivisionRepository $subdivisionRepository,
   EntityTypeManager $entityTypeManager,
   CompanyFieldsService $companyFieldsService,
   CityStampService $cityStampService
  ) {
    $this->subdivisionRepository = $subdivisionRepository;
    $this->entityTypeManager = $entityTypeManager;
    $this->companyFieldsService = $companyFieldsService;
    $this->cityStampService = $cityStampService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new static(
      $container->get('address.subdivision_repository'),
      $container->get('entity_type.manager'),
      $container->get('company.company_fields'),
      $container->get('company.city_stamp_service'),
    );
  }

  /**
   * Creating common fields in city.
   *
   * @param array $form
   *   Define the form variable array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Define the $form_state variable.
   */
  public function createCityFields(array $form, FormStateInterface $form_state): array {
    $form = $this->companyFieldsService->getImagesFields($form, $form_state);

    $isStamp = $form_state->getValue('field_company_unicef_stamp', '');
    $form['field_company_unicef_stamp'] = [
      '#type' => 'select',
      '#title' => $this->t('Does your institution has the UNICEF stamp?'),
      '#attributes' => [
        'placeholder' => $this->t('First select state'),
      ],
      '#options' => [
        '' => $this->t('Select an answer'),
        '0' => $this->t('No'),
        '1' => $this->t('Yes'),
      ],
      '#required' => TRUE,
      '#default_value' => $isStamp,
      '#ajax' => [
        'callback' => [$this, 'reloadFieldAdministrativeArea'],
        'wrapper' => 'form-item-administrative-area',
        'event' => 'change',
      ],
    ];

    $regions = $this->cityStampService->getAllRegions();
    $regionsOptions = [
      '' => $this->t('Select an region'),
    ];
    foreach ($regions as $region) {
      $regionsOptions[$region->id()] = $region->get('name')->getString();
    }

    // Region field for stamp cities (StampYes).
    $selectedRegion = $form_state->getValue('field_company_region', '');
    $form['field_company_region'] = [
      '#type' => 'select',
      '#states' => [
        'visible' => [
          'select[name="field_company_unicef_stamp"]' => ['value' => '1'],
        ],
        'required' => [
          'select[name="field_company_unicef_stamp"]' => ['value' => '1'],
        ],
      ],
      '#title' => $this->t('Which is the region?'),
      '#name' => 'field_company_region',
      '#options' => $regionsOptions,
      '#ajax' => [
        'callback' => [$this, 'reloadFieldAdministrativeArea'],
        'wrapper' => 'form-item-administrative-area',
        'event' => 'change',
      ],
      '#default_value' => $selectedRegion,
    ];

    // Institution type for Non-stamp partners (StampNo).
    $form['field_institution_type'] = [
      '#type' => 'select',
      '#states' => [
        'visible' => [
          'select[name="field_company_unicef_stamp"]' => ['value' => '0'],
        ],
        'required' => [
          'select[name="field_company_unicef_stamp"]' => ['value' => '0'],
        ],
      ],
      '#title' => $this->t('What is your institution type?'),
      '#name' => 'field_institution_type',
      '#options' => [
        'city' => $this->t('County'),
        'state' => $this->t('State'),
      ],
      '#ajax' => [
        'callback' => [$this, 'reloadFieldAdministrativeArea'],
        'wrapper' => 'form-item-administrative-area',
        'event' => 'change',
      ],
    ];

    $statesOptions = ['' => $this->t('Select...')];
    if ($isStamp) {
      if (!empty($selectedRegion)) {
        // If it's stamp, load states based on the taxonomy.
        $states = $this->cityStampService->getStatesByRegion($selectedRegion);
        foreach ($states as $state) {
          $statesOptions[$state->id()] = $state->get('name')->getString();
        }
      }
    }
    else {
      // If it's not stamp, load all states.
      $states = $this->subdivisionRepository->getList(['BR']);
      $statesOptions = array_merge($statesOptions, $states);
    }

    // State field (additional logic are in city_manager_register.js).
    $form['field_company_address']['companyAdministrativeArea'] = [
      '#type' => 'select',
      '#states' => [
        'invisible' => [
          'select[name="field_company_unicef_stamp"]' => ['value' => ''],
        ],
      ],
      '#name' => "companyAdministrativeArea",
      '#title' => $this->t('Administrative area'),
      '#prefix' => '<div id="form-item-administrative-area" class="form-group col-4 form-content-locality-pre-company">',
      '#suffix' => '</div>',
      '#options' => $statesOptions,
      '#ajax' => [
        'callback' => [$this, 'reloadLocality'],
        'wrapper' => 'form-item-locality-company',
        'event' => 'change',
      ],
      '#default_value' => '',
      '#validated' => TRUE,
      '#required' => TRUE,
    ];

    // City field (additional logic are in city_manager_register.js).
    $form['field_company_address']['companyLocality'] = [
      '#type' => 'select',
      '#states' => [
        'invisible' => [
          'select[name="field_company_unicef_stamp"]' => ['value' => ''],
        ],
        'optional' => [
          'select[name="field_institution_type"]' => ['value' => 'state'],
        ],
      ],
      '#name' => "companyLocality",
      '#id' => "locality-company-select",
      '#title' => $this->t('City'),
      '#prefix' => '<div id="form-item-locality-company" class="form-group col-8 form-content-field-city-company">',
      '#suffix' => '</div>',
      '#options' => ['' => $this->t('First select state')],
      '#array_parents' => [
        'field_company_address',
      ],
      '#attributes' => [
        'class' => ['m-0'],
      ],
      '#default_value' => '',
      '#validated' => TRUE,
      '#required' => TRUE,
    ];

    $form = $this->companyFieldsService->getCommitmentTermFields($form, $form_state);

    $form['field_download_commitment_term_infos']['#markup'] = '<p class="text-align-center text-muted" >' . $this->t('Download the commitment term above and use the space below to upload it after signing') . '</p>';
    $form['field_company_commitment_term_infos']['#markup'] = '<br><br><p class="text-align-center text-muted" >' . $this->t('If you prefer you can take this action later. However, your city will only be approved after the upload') . '</p>';

    $form_state->setRebuild();
    return $form;
  }

  /**
   * Set the Ajax configurations for States field based on the institution type.
   *
   * @param array $form
   *   Define the form variable array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Define the $form_state variable.
   *
   * @return array
   *   Modified administrative area with all the proper ajax configurations.
   */
  public function reloadFieldAdministrativeArea(array &$form, FormStateInterface $form_state): array {
    $form['field_company_address']['companyAdministrativeArea']['#required'] = TRUE;
    $form['field_company_address']['companyAdministrativeArea']['#access'] = TRUE;

    $isStamp = $form_state->getValue('field_company_unicef_stamp', FALSE);
    // Prevent unnecessary execution of this method.
    if ($isStamp === '') {
      return $form['field_company_address']['companyAdministrativeArea'];
    }
    $institutionType = $isStamp ? 'city' : $form_state->getValue('field_institution_type');

    $statesOptions = ['' => $this->t('Select...')];
    // If is stamp, load States based on region selected.
    if ($isStamp) {
      $selectedRegion = $form_state->getValue('field_company_region', '');
      if (!empty($selectedRegion)) {
        $states = $this->cityStampService->getStatesByRegion($selectedRegion);
        foreach ($states as $state) {
          $statesOptions[$state->id()] = $state->get('name')->getString();
        }
      }
    }
    else {
      // Otherwise, bring the full list.
      $states = $this->subdivisionRepository->getList(['BR']);
      $statesOptions = array_merge($statesOptions, $states);
    }

    $fieldPrefix = '<div id="form-item-administrative-area" class="form-group form-content-locality-pre-company';

    // Ajax adjustments according to the selected values.
    if ($institutionType === 'city') {
      // Set the ajax that will retrieve the cities list.
      $form['field_company_address']['companyAdministrativeArea']['#ajax'] = [
        'callback' => [$this, 'reloadLocality'],
        'wrapper' => 'form-item-locality-company',
        'event' => 'change',
      ];
      $form['field_company_address']['companyAdministrativeArea']['#prefix'] = $fieldPrefix . ' col-4">';
    }
    elseif ($institutionType === 'state') {
      // Make sure there's no call to the cities ajax.
      $form['field_company_address']['companyAdministrativeArea']['#ajax'] = [];
      $form['field_company_address']['companyAdministrativeArea']['#attached']['drupalSettings'] = [];
      $form['field_company_address']['companyAdministrativeArea']['#prefix'] = $fieldPrefix . '">';
    }

    $form['field_company_address']['companyAdministrativeArea']['#options'] = $statesOptions;

    $form_state->setRebuild();
    return $form['field_company_address']['companyAdministrativeArea'];
  }

  /**
   * Set the options for cities field based on the selected state.
   *
   * @param array $form
   *   Define the form variable array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Define the $form_state variable.
   *
   * @return array
   *   Field administrative area with all cities in selected state.
   */
  public function reloadLocality(array &$form, FormStateInterface $form_state): array {
    $selectedArea = $form_state->getValue('companyAdministrativeArea', '');
    $isStamp = $form_state->getValue('field_company_unicef_stamp', FALSE);

    $citiesOptions = ['' => $this->t('Select...')];
    if (!empty($selectedArea)) {
      if ($isStamp) {
        // If it's stamp, load cities based on the taxonomy.
        $cities = $this->cityStampService->getCitiesByState($selectedArea);
        foreach ($cities as $city) {
          $citiesOptions[$city->id()] = $city->get('name')->getString();
        }
      }
      else {
        // If it's not stamp, load all cities from the selected state.
        $citiesOptions = $this->subdivisionRepository->getList([
          'BR',
          $selectedArea,
        ]);
        $citiesOptions = array_merge(['' => $this->t('Select...')], $citiesOptions);
      }
    }
    $form['field_company_address']['companyLocality']['#options'] = $citiesOptions;
    $form_state->setRebuild();
    return $form['field_company_address']['companyLocality'];
  }

}
