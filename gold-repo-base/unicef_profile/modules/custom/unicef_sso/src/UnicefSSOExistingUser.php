<?php

namespace Drupal\unicef_sso;

/**
 * Logic to handle existing users at SAML login.
 *
 * Existing user: already in the Drupal database, but never logged via SAML yet.
 */
abstract class UnicefSSOExistingUser {

  const MAIL_ATTRIBUTE = 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/emailaddress';

  /**
   * Based on the SAML attributes, decides if it is an existing user or not.
   *
   * @param array $attributes
   *   SAML attributes.
   *
   * @return mixed
   *   FALSE or the user itself.
   */
  public static function fetchExistingUser(array $attributes) {
    if (empty($attributes[self::MAIL_ATTRIBUTE])) {
      return FALSE;
    }

    $existing_users = \Drupal::entityTypeManager()
      ->getStorage('user')
      ->loadByProperties([
        'mail' => $attributes[self::MAIL_ATTRIBUTE],
      ]);

    if ($existing_users) {
      $existing_user = is_array($existing_users) ? reset($existing_users) : FALSE;
      if ($existing_user) {
        return $existing_user;
      }
    }
    return FALSE;
  }

  /**
   * Is the given account allowed to bypass SAML?
   */
  public static function canBypassSaml($account) {
    if (empty($account)) {
      return FALSE;
    }

    // Non-UNICEF users can use their password-based login.
    if (strstr($account->getEmail(), '@unicef.org') === FALSE) {
      return TRUE;
    }

    // And in addition, a curated list of special accounts.
    $local_accounts = \Drupal::config('unicef_sso.config')
      ->get('local_unicef_accounts');
    if (!empty($local_accounts) && is_array($local_accounts)) {
      if (in_array($account->getEmail(), $local_accounts)) {
        return TRUE;
      }
    }

    return FALSE;
  }

}
