<?php

namespace Drupal\umio_user\EventSubscriber;

use Drupal\Core\DrupalKernel;
use Drupal\Core\Session\AccountProxy;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Subscribe to redirect in register routes.
 *
 * @package Drupal\umio_user\EventSubscriber
 */
class UserRegisterRouteSubscriber implements EventSubscriberInterface {

  /**
   * Define the drupalKernel.
   *
   * @var \Drupal\Core\DrupalKernel
   */
  protected $drupalKernel;

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
   */
  final public function __construct(DrupalKernel $drupalKernel, AccountProxy $user) {
    $this->drupalKernel = $drupalKernel;
    $this->user = $user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) : self {
    return new static(
      $container->get('kernel'),
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      KernelEvents::REQUEST => ['onRequest', 50],
    ];
  }

  /**
   * Subscribe to the user login event dispatched.
   *
   * @param \Symfony\Component\HttpKernel\Event\RequestEvent $request
   *   The request object.
   */
  public function onRequest(RequestEvent $request): void {
    $request = $request->getRequest();
    $currentRoute = $request->getPathInfo();

    if ($currentRoute === '/user/register') {
      if ($this->user->isAnonymous()) {
        $url = Url::fromRoute('umio_user.register');
      }
      else {
        $roles = $this->user->getRoles();
        if (in_array('young', $roles)) {
          $url = Url::fromRoute('umio_younger.edit_young', ['user' => $this->user->id()]);
        }
        elseif (in_array('partner_talent_acquisition', $roles)) {
          $url = Url::fromRoute('umio_admin_area.ta_admin.my_account');
        }
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
