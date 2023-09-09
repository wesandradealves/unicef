<?php

namespace Drupal\company_manager\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Class NodePermissionsRouteSubscriber.
 *
 * Listens to the dynamic route events.
 */
class NodePermissionsRouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection): void {
    $route = $collection->get('entity.node.edit_form');
    if ($route) {
      $route = $route->setRequirement(
        '_same_company_node_edit',
        'Drupal\company_manager\Access\SameNodeCompanyEditAccessCheck::access'
      );
    }
    $route = $collection->get('entity.node.canonical');
    if ($route) {
      $route = $route->setRequirement(
        '_same_company_node_view',
        'Drupal\company_manager\Access\SameNodeCompanyViewAccessCheck::access'
      );
    }
  }

}
