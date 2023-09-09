<?php

namespace Drupal\umio_user\EventSubscriber;

use Drupal\Core\DrupalKernel;
use Drupal\Core\Url;
use Drupal\umio_user\Event\UserLoginEvent;
use Drupal\user\UserInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Subscribe to login event and redirect if registration is not completed.
 *
 * @package Drupal\umio_user\EventSubscriber
 */
class UserLoginSubscriber implements EventSubscriberInterface {

  /**
   * Define the drupalKernel.
   *
   * @var \Drupal\Core\DrupalKernel
   */
  protected $drupalKernel;

  /**
   * Define the constructor.
   */
  final public function __construct(DrupalKernel $drupalKernel) {
    $this->drupalKernel = $drupalKernel;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) : self {
    return new static(
      $container->get('kernel')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      UserLoginEvent::EVENT_NAME => 'onUserLogin',
    ];
  }

  /**
   * Subscribe to the user login event dispatched.
   *
   * @param \Drupal\umio_user\Event\UserLoginEvent $event
   *   The event object.
   */
  public function onUserLogin(UserLoginEvent $event): void {
    // Check if user meets the role and did not complete the signup process.
    $user = $event->account;
    if (!$this->checkUserAdditionalRegistration($user)) {
      $roles = $user->getRoles();
      $url = Url::fromRoute('<front>');
      if (in_array('young', $roles)) {
        $url = Url::fromRoute('umio_younger.register_additional');
      }
      elseif (in_array('partner_talent_acquisition', $roles)) {
        if ($user->get('field_flag_public_sector')->getValue()[0]['value']) {
          $url = Url::fromRoute('company_manager.register_city_manager_additional');
        }
        else {
          $url = Url::fromRoute('company_manager.register_aditional');
        }
      }

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

  /**
   * Check user additional registration.
   *
   * @param \Drupal\user\UserInterface $user
   *   The user logged.
   *
   * @return bool
   *   Return if user completed additional registration.
   */
  protected function checkUserAdditionalRegistration(UserInterface $user) {
    $emptyFieldFlagSingup = empty($user->get('field_flag_singup')->getValue());

    if ($emptyFieldFlagSingup) {
      return FALSE;
    }

    return $user->get('field_flag_singup')->getValue()[0]['value'];
  }

}
