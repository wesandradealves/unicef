<?php

namespace Drupal\umio_user\EventSubscriber;

use Drupal\Core\DrupalKernel;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\Core\Session\AccountProxy;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Subscribe to redirect in user edit routes.
 *
 * @package Drupal\umio_user\EventSubscriber
 */
class UserEditRouteSubscriber implements EventSubscriberInterface {

  /**
   * Define the drupalKernel.
   *
   * @var \Drupal\Core\DrupalKernel
   */
  protected $drupalKernel;

  /**
   * Define current route.
   *
   * @var \Drupal\Core\Routing\CurrentRouteMatch
   */
  protected $route;

  /**
   * Define user.
   *
   * @var \Drupal\Core\Session\AccountProxy
   */
  protected $user;

  /**
   * Define the constructor.
   *
   * @param \Drupal\Core\DrupalKernel $drupalKernel
   *   The drupal kernel.
   * @param \Drupal\Core\Session\AccountProxy $user
   *   The current user.
   * @param \Drupal\Core\Routing\CurrentRouteMatch $route
   *   The current route.
   */
  final public function __construct(DrupalKernel $drupalKernel, AccountProxy $user, CurrentRouteMatch $route) {
    $this->drupalKernel = $drupalKernel;
    $this->user = $user;
    $this->route = $route;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) : self {
    return new static(
      $container->get('kernel'),
      $container->get('current_user'),
      $container->get('current_route_match')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      KernelEvents::REQUEST => 'onRequest',
    ];
  }

  /**
   * Subscribe to the user login event dispatched.
   *
   * @param \Symfony\Component\HttpKernel\Event\RequestEvent $request
   *   The request object.
   */
  public function onRequest(RequestEvent $request): void {
    $currentRoute = $this->route->getCurrentRouteMatch()->getRouteName();

    if ($currentRoute === 'entity.user.edit_form') {
      $roles = $this->user->getRoles();
      if (in_array('young', $roles)) {
        $url = Url::fromRoute('umio_younger.edit_young', ['user' => $this->user->id()]);
      }
      elseif (in_array('partner_talent_acquisition', $roles)) {
        $url = Url::fromRoute('umio_admin_area.ta_admin.my_account');
      }

      if (isset($url)) {
        $response = new RedirectResponse($url->toString());
        $request = \Drupal::request();

        // Save the session so things like messages get saved.
        $request->getSession()->save();
        $response->prepare($request);

        // Make sure to trigger kernel events.
        $this->drupalKernel->terminate($request, $response);
        $response->send();
      }
    }
  }

}
