<?php

namespace Drupal\umio_younger\Service;

use Drupal\Core\Session\AccountProxy;
use Drupal\user\Entity\User;

/**
 * Young service for common functions.
 */
class YoungService {

  /**
   * Get the skills from the current user.
   *
   * @param \Drupal\Core\Session\AccountProxy $account
   *   The current user.
   *
   * @return array
   *   The skills id from the current user.
   */
  public function getSkillsFromTheUser(AccountProxy $account): array {
    $user = User::load($account->id());
    $userSkills = $user->get('field_younger_skills')->getValue();
    $skills = [];
    foreach ($userSkills as $skill) {
      $skills[] = (int) $skill['value'];
    }

    return $skills;
  }

  /**
   * Get the priority profile from the current user.
   *
   * @param \Drupal\Core\Session\AccountProxy $account
   *   The current user.
   *
   * @return array
   *   The skills id from the current user.
   */
  public function getPriorityProfileFromTheUser(AccountProxy $account): array {
    $user = User::load($account->id());
    $userProfile = $user->get('field_user_public_profile')->getValue();
    $profiles = [];
    foreach ($userProfile as $profile) {
      $profiles[] = (int) $profile['target_id'];
    }

    return $profiles;
  }

}
