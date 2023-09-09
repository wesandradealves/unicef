<?php

namespace Drupal\umio_front_area\EventSubscriber;

use Drupal\views\Ajax\ViewAjaxResponse;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Response subscriber to handle AJAX responses.
 */
class ViewFilterAjaxResponseSubscriber implements EventSubscriberInterface {

  /**
   * Alter the views filter AJAX response commands.
   *
   * @param array $commands
   *   An array of commands to alter.
   */
  protected function alterFilterCommands(array &$commands): void {
    foreach ($commands as $delta => &$command) {
      if (isset($command['method']) && $command['method'] === 'replaceWith') {
        $command['method'] = 'customizedFilterInsertView';
      }
    }
  }

  /**
   * Renders the ajax commands right before preparing the result.
   *
   * @param \Symfony\Component\HttpKernel\Event\ResponseEvent $event
   *   The response event, which contains the possible AjaxResponse object.
   */
  public function onResponse(ResponseEvent $event): void {
    $response = $event->getResponse();

    // Only alter views ajax responses.
    if (!($response instanceof ViewAjaxResponse)) {
      return;
    }

    $view = $response->getView();
    $allowed_views = [
      'feeds_jovens',
    ];
    $view_info = $view->storage;
    $is_page_request = $event->getRequest()->query->has('page');

    if (!in_array($view_info->id(), $allowed_views) || $is_page_request) {
      return;
    }

    $commands = &$response->getCommands();
    $this->alterFilterCommands($commands);
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [KernelEvents::RESPONSE => [['onResponse']]];
  }

}
