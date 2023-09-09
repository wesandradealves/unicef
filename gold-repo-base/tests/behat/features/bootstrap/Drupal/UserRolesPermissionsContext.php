<?php

namespace Drupal;

use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\TableNode;
use Drupal\DrupalExtension\Context\RawDrupalContext;
use Drupal\user\Entity\Role;
use TravisCarden\BehatTableComparison\TableEqualityAssertion;

/**
 * UserRolePermissionsContext class defines custom step definitions for Behat.
 */
class UserRolesPermissionsContext extends RawDrupalContext implements SnippetAcceptingContext {

  public function __construct() {}

  /**
   * @Then the :permission permission should exist
   */
  public function assertPermissionExists($permission) {
    /** @var \Drupal\user\PermissionHandlerInterface $permission_handler */
    $permission_handler = \Drupal::service('user.permissions');
    if (!array_key_exists($permission, $permission_handler->getPermissions())) {
      throw new \Exception(sprintf('No such permission: %s.', $permission));
    }
  }

  /**
   * @Then the :role_id role should exist
   */
  public function assertRoleExists($role_id) {
    if (!Role::load($role_id)) {
      throw new \Exception(sprintf('No such role: %s.', $role_id));
    }
  }

  /**
   * @Then the :role_id role should have the :permission permission
   */
  public function assertRoleHasPermission($role_id, $permission) {
    $this->assertRoleExists($role_id);
    $this->assertPermissionExists($permission);

    /** @var \Drupal\user\RoleInterface $role */
    $role = Role::load($role_id);

    if (!$role->hasPermission($permission)) {
      throw new \Exception(sprintf('The "%s" role does not have the the "%s" permission.', $role_id, $permission));
    }
  }

  /**
   * @Then the :role_id role should have exactly the following permissions
   */
  public function assertRoleHasPermissions($role_id, TableNode $expected) {
    $this->assertRoleExists($role_id);

    /** @var \Drupal\user\RoleInterface $role */
    $role = Role::load($role_id);
    $actual = self::getTableFromList($role->getPermissions());

    (new TableEqualityAssertion($expected, $actual))
      ->ignoreRowOrder()
      ->setMissingRowsLabel('Missing permissions')
      ->setUnexpectedRowsLabel('Unexpected permissions')
      ->assert();
  }

  /**
   * @Then the :role_id role should be the administrator role
   */
  public function assertRoleIsAdministrator($role_id) {
    $this->assertRoleExists($role_id);

    /** @var \Drupal\user\RoleInterface $role */
    $role = Role::load($role_id);
    if (!$role->isAdmin()) {
      throw new \Exception(sprintf('The "%s" role is not the administrator role.', $role_id));
    }
  }

  /**
   * @Then exactly the following roles should exist
   */
  public function assertRolesExist(TableNode $expected) {
    $roles = [];
    /** @var \Drupal\user\Entity\Role $role */
    foreach (Role::loadMultiple() as $id => $role) {
      $roles[] = [$role->label(), $id];
    }
    $actual = new TableNode($roles);

    (new TableEqualityAssertion($expected, $actual))
      ->expectHeader(['label', 'machine name'])
      ->ignoreRowOrder()
      ->setMissingRowsLabel('Missing roles')
      ->setUnexpectedRowsLabel('Unexpected roles')
      ->assert();
  }

  /**
   * Converts a given list (a one-dimensional array) to a table.
   *
   * @param array $list
   *   The list to convert.
   *
   * @return \Behat\Gherkin\Node\TableNode The table.
   *   The table.
   */
  public static function getTableFromList(array $list) {
    assert(count($list) === count($list, COUNT_RECURSIVE), 'List must be a one-dimensional array.');

    array_walk($list, function (&$item) {
      $item = [$item];
    });
    return new TableNode($list);
  }

}
