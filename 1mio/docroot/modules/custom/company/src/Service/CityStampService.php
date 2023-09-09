<?php

namespace Drupal\company\Service;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\taxonomy\Entity\Term;
use Drupal\views\Views;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Service with helpers function to handle with company content type.
 */
class CityStampService {

  /**
   * Name of the vocabulary that saves city stamps.
   *
   * @var string
   */
  const STAMP_VOCABULARY = 'stamp_unicef';

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * The term storage.
   *
   * @var \Drupal\taxonomy\TermStorage
   */
  private $termStorage;

  /**
   * The term storage.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  private $timeService;

  /**
   * The construct method.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\Component\Datetime\TimeInterface $timeService
   *   The time service.
   */
  final public function __construct(EntityTypeManagerInterface $entityTypeManager, TimeInterface $timeService) {
    $this->entityTypeManager = $entityTypeManager;
    $this->termStorage = $this->entityTypeManager->getStorage('taxonomy_term');
    $this->timeService = $timeService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('datetime.time'),
    );
  }

  /**
   * Get all regions.
   *
   * @return \Drupal\taxonomy\Entity\Term[]
   *   Return an array with all regions terms.
   */
  public function getAllRegions(): array {
    $terms = $this->termStorage->loadTree(
      self::STAMP_VOCABULARY,
      0,
      1,
      TRUE
    );

    return $terms;
  }

  /**
   * Get all states by the region.
   *
   * @param int $regionTid
   *   The region id to get the states.
   *
   * @return array
   *   Return an array (tid) with all states terms.
   */
  public function getStatesByRegion(int $regionTid): array {
    $terms = $this->termStorage->loadByProperties([
      'vid'   => self::STAMP_VOCABULARY,
      'parent' => $regionTid,
    ]);

    return $terms;
  }

  /**
   * Get all states by the region.
   *
   * @param int $stateTid
   *   The state to get the cities.
   *
   * @return array
   *   Return an array (tid) with all states terms.
   */
  public function getCitiesByState(int $stateTid): array {
    $terms = $this->termStorage->loadByProperties([
      'vid'   => self::STAMP_VOCABULARY,
      'parent' => $stateTid,
    ]);

    return $terms;
  }

  /**
   * Get term by Id.
   *
   * @param int $termTid
   *   The state to get the cities.
   *
   * @return \Drupal\taxonomy\Entity\Term|null
   *   Return an term.
   */
  public function getTermById(int $termTid): ?Term {
    $term = Term::load($termTid);

    return $term ? $term : NULL;
  }

  /**
   * Get states.
   *
   * @return array
   *   Return array with states of partners.
   */
  public function getFormatedStates() :array {
    $viewStates = Views::getView('states');
    $viewStates->setItemsPerPage(30);
    $viewStates->setCurrentPage(0);
    $viewStates->executeDisplay('default');

    $arrayStates = [];
    /** @var \Drupal\views\ResultRow $row */
    foreach ($viewStates->result as $row) {
      /** @var \Drupal\taxonomy\Entity\Term $entity */
      $entity = $row->_entity;
      $arrayStates[] = [
        'id' => $entity->get('tid')->getString(),
        'name' => $entity->label(),
      ];
    }

    return $arrayStates;
  }

  /**
   * Get states.
   *
   * @return array
   *   Return array with states of partners on a specific region.
   */
  public function getFormatedStatesByRegion(int $regionTid): array {
    $statesTerms = $this->getStatesByRegion($regionTid);
    $statesData = [];
    foreach ($statesTerms as $statesTerm) {
      $statesData[] = [
        'id' => $statesTerm->tid->getString(),
        'name' => $statesTerm->name->getString(),
      ];
    }
    return $statesData;
  }

  /**
   * Get regions.
   *
   * @return array
   *   Return array with regions of partners.
   */
  public function getFormatedRegions(): array {
    $regionTerms = $this->getAllRegions();
    $regionsData = [];
    foreach ($regionTerms as $regionTerm) {
      $regionsData[] = [
        'id' => $regionTerm->tid->getString(),
        'name' => $regionTerm->name->getString(),
      ];
    }
    return $regionsData;
  }

  /**
   * Update stamp term setting a join date.
   *
   * @param string $stateName
   *   The state.
   * @param string $cityName
   *   The city.
   * @param string $createdDate
   *   The created timeStamp.
   */
  public function updateStampTermDate(string $stateName, string $cityName, string $createdDate = NULL): void {
    $fieldUsedToFindState = 'name';
    if (strlen($stateName) === 2) {
      $fieldUsedToFindState = 'field_stamp_unicef_acronym';
    }
    $statesRaw = $this->termStorage->loadByProperties([
      'vid'   => self::STAMP_VOCABULARY,
      $fieldUsedToFindState => $stateName,
    ]);
    $state = array_shift($statesRaw);
    if ($state) {
      $citiesRaw = $this->termStorage->loadByProperties([
        'vid'   => self::STAMP_VOCABULARY,
        'parent' => $state->id(),
        'name' => $cityName,
      ]);
      if ($createdDate === NULL) {
        $createdDate = $this->timeService->getRequestTime();
      }
      /** @var \Drupal\taxonomy\Entity\Term $city */
      $city = array_shift($citiesRaw);
      if (!empty($city)) {
        $city->set('name', $cityName);
        $city->set('field_stamp_unicef_join_date', $createdDate);
        $city->save();
      }
    }
  }

}
