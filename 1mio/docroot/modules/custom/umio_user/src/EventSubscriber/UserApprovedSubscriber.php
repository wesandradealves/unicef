<?php

namespace Drupal\umio_user\EventSubscriber;

use Drupal\Core\DrupalKernel;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\Core\Session\AccountProxy;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;
use Drupal\umio_user\UserStatusTrait;
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
class UserApprovedSubscriber implements EventSubscriberInterface {

  use UserStatusTrait;
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
      KernelEvents::REQUEST => 'approvedCheck',
    ];
  }

  /**
   * Check if the user is approved to acces this routes.
   */
  public function approvedCheck(): void {
    $currentRoute = $this->route->getCurrentRouteMatch()->getRouteName();
    $currentUser = User::load($this->user->id());
    $roles = $currentUser->getRoles();
    $status = $this->getUserStatus($currentUser);
    $approvedStatus = ($status && $status === $this->getActiveStatus());
    $completeRegister = $this->checkUserAdditionalRegistration($currentUser);
    $restrictedRoutes = $this->getRestrictedRoutes($currentUser);

    if ($completeRegister && !$approvedStatus && in_array($currentRoute, $restrictedRoutes) && in_array('partner_talent_acquisition', $roles)) {
      $url = Url::fromRoute('<front>');
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

  /**
   * Check routes that user can acces.
   *
   * @param \Drupal\user\UserInterface $user
   *   The user logged.
   *
   * @return array
   *   Return routes.
   */
  protected function getRestrictedRoutes(UserInterface $user): array {
    $approvedRoutes = [
      'view.feeds_jovens.feed_general',
      'view.frontpage.page_1',
      'umio_admin_area.ta_admin.vacancies',
      'umio_admin_area.ta_admin.courses',
      'umio_admin_area.ta_admin.company.branches',
      'umio_admin_area.ta_admin.company_people',
      'umio_admin_area.ta_admin.vacancies',
      'umio_admin_area.ta_admin.dashboard',
    ];
    $company = Node::load($user->get('field_user_company')->getString());

    if ($company && ($company->getOwnerId() !== $user->id())) {
      array_push($approvedRoutes, 'umio_admin_area.ta_admin.company');
    }

    return $approvedRoutes;
  }

}
