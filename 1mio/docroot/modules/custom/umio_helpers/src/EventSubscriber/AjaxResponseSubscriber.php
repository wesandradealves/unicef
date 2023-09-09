<?php

namespace Drupal\umio_helpers\EventSubscriber;

use Drupal\views\Ajax\ViewAjaxResponse;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Response subscriber to handle AJAX responses.
 */
class AjaxResponseSubscriber implements EventSubscriberInterface {

  /**
   * Alter the views AJAX response commands only for the infinite pager.
   *
   * @param array $commands
   *   An array of commands to alter.
   */
  protected function alterPaginationCommands(array &$commands) : void {
    foreach ($commands as $delta => &$command) {
      // Substitute the 'replace' method with our custom jQuery method which
      // will allow views content to be injected one after the other.
      if (isset($command['method']) && $command['method'] === 'replaceWith') {
        $command['method'] = 'customizedInfiniteScrollInsertView';
      }
    }
  }

  /**
   * Renders the ajax commands right before preparing the result.
   *
   * @param \Symfony\Component\HttpKernel\Event\ResponseEvent $event
   *   The response event, which contains the possible AjaxResponse object.
   */
  public function onResponse(ResponseEvent $event) : void {
    $response = $event->getResponse();

    // Only alter views ajax responses.
    if (!($response instanceof ViewAjaxResponse)) {
      return;
    }

    $view = $response->getView();
    $allowed_views = [
      'feeds_jovens',
    ];
    $viewName = $view->id();

    // Only alter commands if the user has selected our pager and it attempting
    // to move beyond page 0.
    if ($view->getPager()->getPluginId() !== 'infinite_scroll' ||
          $view->getCurrentPage() === 0 ||
          $view->getPager()->getCurrentPage() === 0 ||
          !in_array($viewName, $allowed_views)) {
      // When the current page is 0 it might be the case that there where no
      // additional items in this case we want to still append the empty result.
      return;
    }

    $commands = &$response->getCommands();
    $this->alterPaginationCommands($commands);
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [KernelEvents::RESPONSE => [['onResponse']]];
  }

}
