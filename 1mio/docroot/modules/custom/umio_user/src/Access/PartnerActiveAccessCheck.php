<?php

namespace Drupal\umio_user\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\umio_user\UserStatusTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Check if the partner user is actived.
 */
class PartnerActiveAccessCheck implements AccessInterface {

  use UserStatusTrait;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * The construct method.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   */
  final public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * A custom access check.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   Run access checks for this account.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function access(AccountInterface $account) {
    if ($account->isAuthenticated()) {
      $roles = $account->getRoles();
      if (in_array('administrator', $roles) ||
          in_array('company_manager', $roles)
      ) {
        return AccessResult::allowed();
      }

      if (in_array('partner_talent_acquisition', $roles)) {
        /** @var \Drupal\user\Entity\User|null $user */
        $user = $this->entityTypeManager->getStorage('user')->load($account->id());
        if (!$user) {
          // Other class or services will throw error.
          return AccessResult::neutral();
        }
        $status = $this->getUserStatus($user);
        if ($status && $status === $this->getActiveStatus()) {
          return AccessResult::allowed();
        }
      }
    }

    return AccessResult::forbidden();
  }

}
