<?php

namespace Drupal\umio_batch_import\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\umio_batch_import\Form\AbstractBatchImportForm;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Service to run phpoffice lib helpers functions.
 */
class PhpOfficeService {

  use MessengerTrait;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * The file system.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  private $fileSystem;

  /**
   * The file url generator service.
   *
   * @var \Drupal\Core\File\FileUrlGeneratorInterface
   */
  private $fileUrlGeneratorInterface;

  /**
   * Constructs a \Drupal\umio_batch_import\Service\PhpOfficeService object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\Core\File\FileSystemInterface $fileSystem
   *   The file system.
   * @param \Drupal\Core\File\FileUrlGeneratorInterface $fileUrlGeneratorInterface
   *   The file url generator service.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, FileSystemInterface $fileSystem, FileUrlGeneratorInterface $fileUrlGeneratorInterface) {
    $this->entityTypeManager = $entityTypeManager;
    $this->fileSystem = $fileSystem;
    $this->fileUrlGeneratorInterface = $fileUrlGeneratorInterface;
  }

  /**
   * Get the header of the template spreeadsheet.
   *
   * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $spreadsheet
   *   The current array spreadsheet.
   *
   * @return array
   *   Returns the header of the spreeadsheet.
   */
  public function getHeaderSpreeadSheet(Worksheet $spreadsheet): array {
    $header = [];
    // Iterator to only the first row.
    $rowIterator = $spreadsheet->getRowIterator(1, 1);
    foreach ($rowIterator as $row) {
      $cellIterator = $row->getCellIterator();
      $cellIterator->setIterateOnlyExistingCells(TRUE);
      $header = [];
      foreach ($cellIterator as $cellKey => $cell) {
        $header[$cellKey] = $cell->getValue();
      }
    }

    return $header;
  }

  /**
   * Load the submited spreadsheet.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return \PhpOffice\PhpSpreadsheet\Spreadsheet
   *   The submited spreadsheet.
   */
  public function loadSpreadsheet(FormStateInterface $form_state): Spreadsheet {
    $file = $form_state->getValue('file')[0];
    /** @var \Drupal\file\Entity\File $file */
    $file = $this->entityTypeManager->getStorage('file')->load($file);
    $filePath = $this->fileSystem->realpath($file->getFileUri());
    return IOFactory::load($filePath);
  }

  /**
   * Function to delete the spreadsheet file after read it.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function deleteSpreadSheet(FormStateInterface $form_state): void {
    $file = $form_state->getValue('file')[0];
    /** @var \Drupal\file\Entity\File $file */
    $file = $this->entityTypeManager->getStorage('file')->load($file);
    $file->delete();
  }

  /**
   * Create the error spreadsheet.
   *
   * @param \PhpOffice\PhpSpreadsheet\Spreadsheet $spreadsheet
   *   The submited spreadsheet.
   */
  public function createErrorSpreadsheet(Spreadsheet $spreadsheet): void {
    $writer = new Xlsx($spreadsheet);
    $hashing_filename = md5($spreadsheet->getID() . time());
    $filePath = AbstractBatchImportForm::DIRECTORY_UPLOAD . $hashing_filename . '.xlsx';
    $writer->save($filePath);
    $fileExternalLink = $this->fileUrlGeneratorInterface->generateString($filePath);
    $this->messenger()->addMessage($fileExternalLink, 'content-import-validated-sheet-link');
  }

}
