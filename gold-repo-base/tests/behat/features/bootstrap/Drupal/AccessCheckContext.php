<?php

namespace Drupal;

use Drupal\DrupalExtension\Context\RawDrupalContext;

/**
 * AccessCheckContext class defines custom steps for access checks.
 */
class AccessCheckContext extends RawDrupalContext {

  /**
   * Visit a given path, and additionally check for HTTP response code 200.
   *
   * @Given I am at the following :path
   * @When I visit the following :path
   */
  public function assertAtPath($path) {
    $this->getSession()->visit($this->locatePath($path));
  }

  /**
   * Checks for the access or the lack of the access.
   *
   * @Then I :access have view access
   */
  public function iHaveViewAccess($access) {
    if ($access == 'do') {
      $this->iShouldNotSeeAccessDeniedMessage();
    }
    else {
      $this->iShouldSeeAccessDeniedMessage();
    }
  }

  /**
   * Checks for "ok" HTTP status codes.
   *
   * @Then I should receive 2xx status code.
   */
  public function iShouldNotSeeAccessDeniedMessage() {
    $response_code = (string) $this->getSession()->getStatusCode();

    if ($response_code[0] != 2) {
      throw new \Exception("Access denied");
    }
  }

  /**
   * Checks for "denied" HTTP status codes.
   *
   * @Then I should receive 4xx status code.
   */
  public function iShouldSeeAccessDeniedMessage() {
    $response_code = (string) $this->getSession()->getStatusCode();

    if ($response_code[0] != 4) {
      throw new \Exception("Access was not denied");
    }
  }

}
