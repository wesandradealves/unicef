---------------------

 * Introduction
 * Requirements
 * Installation

INTRODUCTION
------------

 * unicef_sso module is used to customizations of the SSO/SAML integration for the UNICEF websites.
 * Module File:
   - unicef_sso_simplesamlphp_auth_existing_user() : When user try to use saml without account in unicef site, which will throw error.
   - unicef_sso_form_user_login_form_alter() : Adds sso login and reset password link to the login form.
   - unicef_sso_form_user_pass_alter(): Adds validation only for anonymous and when saml settings is activated. Force unicef users to use sso login.
   - unicef_sso_menu_local_tasks_alter(): Adds menu tab to the login page.
 * src/UnicefSSOExistingUser.php File:
   - Logic to handle existing users at SAML login.
   - fetchExistingUser() : Based on the SAML attributes, decides if it is an existing user or not.
   - canBypassSaml() : checks whether the account can bypass saml login or not.

 REQUIREMENTS
------------
This module requires the following modules:

 * simplesamlphp_auth (https://www.drupal.org/project/simplesamlphp_auth)

INSTALLATION
------------

 * Install as you would normally install a contributed Drupal module.
