<?php

namespace Drupal\umio_user\EventSubscriber;

use Drupal\Core\DrupalKernel;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\Core\Session\AccountProxy;
use Drupal\Core\Url;
use Drupal\user\Entity\User;
use Drupal\user\UserInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Check acces to restrict route without additional register done.
 *
 * @package Drupal\umio_user\EventSubscriber
 */
class UserRoutePermissionSubscriber implements EventSubscriberInterface {

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
   * Define route.
   *
   * @var \Drupal\Core\Routing\CurrentRouteMatch
   */
  protected $route;

  /**
   * Define the restricted routes without complete register.
   */
  const RESTRICTED_ROUTES = [
    'view.social_feed.social_feed_display',
    'view.feeds_jovens.feed_general',
    'view.frontpage.page_1',
    'umio_admin_area.ta_admin.vacancies',
    'umio_admin_area.ta_admin.courses',
    'umio_admin_area.ta_admin.company',
    'umio_admin_area.ta_admin.company.branches',
    'umio_admin_area.ta_admin.company_people',
    'umio_admin_area.ta_admin.vacancies',
    'umio_admin_area.ta_admin.dashboard',
    'tfa.overview',
    'user.page',
  ];

  /**
   * Define the constructor.
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
      $container->get('current_route_match'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      KernelEvents::REQUEST => 'restrictRouteCheck',
    ];
  }

  /**
   * Subscribe to the user login event dispatched.
   */
  public function restrictRouteCheck(): void {
    $currentRoute = $this->route->getCurrentRouteMatch()->getRouteName();
    $currentUser = User::load($this->user->id());
    $roles = $currentUser->getRoles();
    $completeRegister = $this->checkUserAdditionalRegistration($currentUser);
    if (!$completeRegister && in_array($currentRoute, self::RESTRICTED_ROUTES) && in_array('partner_talent_acquisition', $roles)) {
      $url = $this->validadeTalentAcquisitionAcces($currentUser);
      $response = new RedirectResponse($url->toString());
      $request = \Drupal::request();

      // Save the session so things like messages get saved.
      $request->getSession()->save();
      $response->prepare($request);

      // Make sure to trigger kernel events.
      $this->drupalKernel->terminate($request, $response);
      $response->send();
    }
    elseif (!$completeRegister && in_array('young', $roles) && ($currentRoute != 'umio_younger.register_additional')) {
      $url = Url::fromRoute('umio_younger.register_additional');
      $response = new RedirectResponse($url->toString());
      $response->send();
    }

  }

  /**
   * Check Talent Acquisition acces.
   *
   * @param \Drupal\user\UserInterface $user
   *   The user logged.
   *
   * @return \Drupal\Core\Url
   *   Return url object.
   */
  protected function validadeTalentAcquisitionAcces(UserInterface $user): Url {
    $publicSector = $user->get('field_flag_public_sector')->getValue()[0]['value'];
    if ($publicSector) {
      return Url::fromRoute('company_manager.register_city_manager_additional');
    }
    return Url::fromRoute('company_manager.register_aditional');
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
