<?php

namespace Drupal\umio_batch_import\Service;

use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Service to create a batch operations.
 */
class BatchOperationService {

  use StringTranslationTrait;

  /**
   * Function to create a batch to execute in an array of data.
   *
   * @param array $data
   *   The data to be executed in batch.
   * @param array $methodToExecute
   *   The method that are gonna to be executed in every data.
   */
  public function batchOperation(array $data, array $methodToExecute): void {

    $batch = [
      'title' => $this->t('Saving Data'),
      'operations' => [],
      'init_message' => $this->t('Import process is starting.'),
      'progress_message' => $this->t('Processed @current out of @total. Estimated time: @estimate.'),
      'error_message' => $this->t('The process has encountered an error.'),
    ];

    foreach ($data as $item) {
      $batch['operations'][] = [
        $methodToExecute,
        [$item],
      ];
    }

    batch_set($batch);
  }

}
