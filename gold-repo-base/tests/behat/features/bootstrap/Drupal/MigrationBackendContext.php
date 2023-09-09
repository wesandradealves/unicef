<?php

namespace Drupal;

use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Drupal\DrupalExtension\Context\MinkContext;

/**
 * UnicefMigrationUiContext for testing steps via ui.
 */
class UnicefMigrationUiContext extends RawDrupalContext implements SnippetAcceptingContext {

  protected $minkContext;

  /**
   * Pre-scenario hook.
   *
   * @BeforeScenario
   */
  public function gatherContexts(BeforeScenarioScope $scope) {
    $env = $scope->getEnvironment();
    $this->minkContext = $env->getContext(MinkContext::class);
  }

  /**
   * @When (I )open the UI for :a_migration
   */
  public function iOpenMigrationBackendFor($a_migration) {
    // the paths in the core migration backend correspond to the plugin names
    // so since we are verbosely passing in the actual migration ids to check
    // from gherkin we dont need to lookup all existing migrations
    // to infer paths but can go with the much simpler and more transparent
    // string interpolation.

    $path = "/admin/structure/migrate/manage/${a_migration}/migrations";
    $this->minkContext->assertAtPath($path);

  }

}
