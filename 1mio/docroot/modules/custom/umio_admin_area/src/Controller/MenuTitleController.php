<?php

namespace Drupal\umio_admin_area\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Drupal\umio_user\Service\UserService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class to return the page title dynamic.
 */
class MenuTitleController extends ControllerBase {

  /**
   * Define the user service.
   *
   * @var \Drupal\umio_user\Service\UserService
   */
  private $userService;

  /**
   * Define the constructor.
   *
   * @param \Drupal\umio_user\Service\UserService $userService
   *   The user service.
   */
  final public function __construct(UserService $userService) {
    $this->userService = $userService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) : self {
    return new static(
      $container->get('umio_user.user_service'),
    );
  }

  /**
   * Returns the page title.
   *
   * @return string
   *   Returns a string with page title.
   */
  public function getTitle(): string {
    $userMainCompany = $this->userService->getCurrentUserMainCompany();
    $userMainCompany = Node::Load($userMainCompany);
    $publicSector = [
      'city',
      'state',
    ];
    if (in_array($userMainCompany->get('field_company_type')->getString(), $publicSector)) {
      return $this->t('Public Sector data');
    }
    elseif ($userMainCompany->get('field_company_type')->getString() == 'civil-society') {
      return $this->t('Civil Society data');
    }
    return $this->t('Company data');
  }

}
