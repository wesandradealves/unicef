<?php

namespace Drupal\umio_front_area\Controller;

use Drupal\company\Service\CityStampService;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\Renderer;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\views\Views;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Returns responses for 1mio Front Area routes.
 */
class PartnersCityPanelViewController extends ControllerBase {

  /**
   * Define the renderer.
   *
   * @var \Drupal\Core\Render\Renderer
   */
  private $renderer;

  /**
   * Define the cityStampService.
   *
   * @var \Drupal\company\Service\CityStampService
   */
  protected $cityStampService;

  /**
   * Define the constructor.
   */
  final public function __construct(Renderer $renderer, CityStampService $cityStampService) {
    $this->renderer = $renderer;
    $this->cityStampService = $cityStampService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) : self {
    return new static(
      $container->get('renderer'),
      $container->get('company.city_stamp_service')
    );
  }

  /**
   * Get states.
   *
   * @return array
   *   Return array with states of partners.
   */
  public function getStates() :array {
    $viewStates = Views::getView('states');
    $viewStates->setItemsPerPage(30);
    $viewStates->setCurrentPage(0);
    $viewStates->executeDisplay('default');

    $arrayStates = [];
    /** @var \Drupal\views\ResultRow $row */
    foreach ($viewStates->result as $row) {
      /** @var \Drupal\taxonomy\Entity\Term $entity */
      $entity = $row->_entity;
      $arrayStates[$entity->get('tid')->getString()] = $entity->label();
    }

    return $arrayStates;
  }

  /**
   * Builds the response.
   *
   * @return array
   *   The response.
   */
  public function build() :array {

    $view = Views::getView('partner_cities');
    $view->setDisplay('default');
    $view->setAjaxEnabled(FALSE);

    $filters = $view->getDisplay()->getOption('filters');
    $filters["parent_target_id"]['group_info']['group_items'] = [];
    foreach ($this->getStates() as $id => $name) {
      $filters["parent_target_id"]['group_info']['group_items'][] = [
        "title" => (string) $name,
        "operator" => "=",
        "value" => [
          "value" => $id,
          "min" => "",
          "max" => "",
        ],
      ];
    }

    $view->getDisplay()->overrideOption('filters', $filters);
    $view->execute();
    $rendered = $view->render();
    unset($rendered['#view']->exposed_widgets['#action']);

    $build['content'] = [
      '#type' => 'item',
      '#markup' => $this->renderer->render($rendered),
    ];

    return $build;
  }

  /**
   * Update partner cities join date.
   */
  public function updatePartnerCities(): RedirectResponse {
    $cities = \Drupal::entityTypeManager()
      ->getStorage('node')
      ->loadByProperties(
        [
          'type' => 'company',
          'field_company_type' => 'city',
        ]
      );
    foreach ($cities as $city) {
      /** @var \Drupal\node\Entity\Node $city */
      $createdDate = $city->get('created')->getString();
      /** @var \Drupal\entity_reference_revisions\EntityReferenceRevisionsFieldItemList $fieldCompanyAddress */
      $fieldCompanyAddress = $city->get('field_company_address');
      $targetId = $fieldCompanyAddress->target_id;
      $cityAddress = Paragraph::load($targetId);
      if ($cityAddress) {
        $cityAddressArray = $cityAddress->get('field_paragraph_address')->getValue()[0];
        $stateKey = $cityAddressArray['administrative_area'];
        $cityKey = $cityAddressArray['locality'];
        $this->cityStampService->updateStampTermDate($stateKey, $cityKey, $createdDate);
      }
    }
    return $this->redirect('umio_front_area.partners_cities');
  }

}
