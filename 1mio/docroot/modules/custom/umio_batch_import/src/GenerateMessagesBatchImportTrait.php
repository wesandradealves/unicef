<?php

namespace Drupal\umio_batch_import;

use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\umio_batch_import\Form\AbstractBatchImportForm;

/**
 * Provides a Content Importer 1Mio form.
 */
trait GenerateMessagesBatchImportTrait {

  use MessengerTrait;
  use StringTranslationTrait;

  /**
   * Function to generate a markup field to show success and/or errors messages.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   */
  public function generateStatusMarkupField(array &$form): array {
    $markup = '';
    $markupSuccess = $this->generateSuccessMarkup();
    $markupError = $this->generateErrorMarkup();

    if ($markupSuccess && $markupError) {
      $markup = $markupSuccess . '<hr class="my-3">' . $markupError;
    }
    elseif ($markupError) {
      $markup = $markupError;
    }
    elseif ($markupSuccess) {
      $markup = $markupSuccess;
    }

    if ($markup) {
      $form[AbstractBatchImportForm::STATUS_FIELD] = [
        '#prefix' => '<div class="px-4 px-md-5 messages content-importer-messages">',
        '#markup' => $markup,
        '#suffix' => '</div>',
      ];
    }

    return $form;
  }

  /**
   * Function to return HTML for success messages.
   *
   * @return string
   *   Markup with error HTML text.
   */
  private function generateSuccessMarkup(): string {
    $messageSuccess = $this->messenger()->messagesByType(AbstractBatchImportForm::TYPE_SUCCESS_MESSAGE);
    $this->messenger()->deleteByType(AbstractBatchImportForm::TYPE_SUCCESS_MESSAGE);
    $markup = '';
    if (!empty($messageSuccess)) {
      $icon = '<i class="ph-check-circle-bold text-success"></i>';
      $markup = <<<EOT
        <div class="row position-relative">
          $icon
          <div class="ps-5">
            <h6 class="text-success fw-bold">Sucesso</h6>
            $messageSuccess[0]
          </div>
        </div>
      EOT;
    }

    return $markup;
  }

  /**
   * Function to return HTML for error messages.
   *
   * @return string
   *   Markup with error HTML text.
   */
  private function generateErrorMarkup(): string {
    $messageError = $this->messenger()->messagesByType(AbstractBatchImportForm::TYPE_ERROR_MESSAGE);
    $validatedSheetLink = $this->messenger()->messagesByType(AbstractBatchImportForm::TYPE_VALIDATED_SHEET_LINK);
    $this->messenger()->deleteByType(AbstractBatchImportForm::TYPE_ERROR_MESSAGE);
    $this->messenger()->deleteByType(AbstractBatchImportForm::TYPE_VALIDATED_SHEET_LINK);
    $markup = '';
    if ($messageError) {
      $icon = '<i class="ph-x-circle-bold text-danger"></i>';
      $textButton = $this->t('Download sheet');

      $markup = <<<EOT
        <div class="row position-relative">
          $icon
          <div class="col-12 col-md-6 col-lg-9 ps-5">
            <h6 class="text-danger fw-bold">Erro</h6>
            $messageError[0]
          </div>
          <div class="col-12 col-md-6 col-lg-3 mt-3 ps-md-5 text-end d-flex justify-content-md-end align-items-center">
            <a class="btn btn-primary w-100" href="$validatedSheetLink[0]">$textButton</a>
          </div>
        </div>
      EOT;
    }

    return $markup;
  }

}
