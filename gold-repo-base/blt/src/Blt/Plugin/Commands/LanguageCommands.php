<?php

namespace Example\Blt\Plugin\Commands;

use Acquia\Blt\Robo\BltTasks;
use Symfony\Component\Console\Input\InputOption;

/**
 * Defines commands in the "language" namespace.
 */
class LanguageCommands extends BltTasks {

  /**
   * Create language for unicef sites.
   *
   * @command language:add
   * @description This will add language to any sites.
   */
  public function addLanguage(array $options = [
    'site-name' => InputOption::VALUE_REQUIRED,
    'language-code' => InputOption::VALUE_REQUIRED,
    'language-weight' => InputOption::VALUE_REQUIRED,
    'language-label' => InputOption::VALUE_REQUIRED,
  ]) {
    $this->taskExec('./script-add-language.sh ' . $options['site-name'] . " " . $options['language-code'] . " " . $options['language-weight'] . " " . $options['language-label'])
      ->dir($this->getConfigValue('repo.root') . "/docroot/scripts")
      ->run();

  }

}
