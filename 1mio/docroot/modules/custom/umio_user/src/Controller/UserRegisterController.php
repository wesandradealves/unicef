<?php

namespace Drupal\umio_user\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Routing\CurrentRouteMatch;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller to render twig after submit register company manager webform.
 */
class UserRegisterController extends ControllerBase {

  /**
   * The current route.
   *
   * @var \Drupal\Core\Routing\CurrentRouteMatch
   */
  private $currentRouteMatch;

  /**
   * The construct method.
   *
   * @param \Drupal\Core\Routing\CurrentRouteMatch $currentRouteMatch
   *   The current route.
   */
  final public function __construct(CurrentRouteMatch $currentRouteMatch) {
    $this->currentRouteMatch = $currentRouteMatch;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new static(
      $container->get('current_route_match'),
    );
  }

  /**
   * Display the success page with a link to register company page.
   *
   * @return array
   *   Return markup array.
   */
  public function registerPortal() {
    return [
      '#theme' => 'page_pre_register_user',
    ];
  }

  /**
   * Build the email page response.
   *
   * @return array
   *   Return markup array.
   */
  public function emailConfirmation(): array {
    $email = $this->currentRouteMatch->getParameter('email');
    return [
      '#theme' => 'page_email_confirmation',
      '#email' => $email,
    ];
  }

}
