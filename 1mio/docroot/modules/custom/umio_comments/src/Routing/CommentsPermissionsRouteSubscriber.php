<?php

namespace Drupal\umio_comments\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Class CommentsPermissionsRouteSubscriber.
 *
 * Listens to the dynamic route events.
 */
class CommentsPermissionsRouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection): void {
    $route = $collection->get('entity.comment.delete_form');
    if ($route) {
      $requirements = $route->getRequirements();
      unset($requirements['_entity_access']);
      $route = $route->setRequirements(
        array_merge($requirements, [
          '_comment_delete' => 'Drupal\umio_comments\Access\CommentsDeleteAccessCheck::access',
        ])
      );
    }
  }

}
