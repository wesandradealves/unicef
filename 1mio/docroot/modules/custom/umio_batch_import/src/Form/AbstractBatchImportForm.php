<?php

namespace Drupal\umio_batch_import\Form;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EditorialContentEntityBase;
use Drupal\Core\Entity\EntityConstraintViolationListInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\umio_batch_import\GenerateMessagesBatchImportTrait;
use Drupal\umio_batch_import\Service\BatchImportTaxonomyService;
use Drupal\umio_batch_import\Service\BatchOperationService;
use Drupal\umio_batch_import\Service\PhpOfficeService;
use Drupal\umio_user\Service\UserService;
use Drupal\umio_helpers\Service\AddressService;
use PhpOffice\PhpSpreadsheet\Worksheet\Row;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a Content Importer 1MIO form.
 */
abstract class AbstractBatchImportForm extends FormBase {

  use GenerateMessagesBatchImportTrait;

  /**
   * Directory to save spreadsheet.
   *
   * @var string
   */
  const DIRECTORY_UPLOAD = 'public://content/batch-import-vacancy/';

  /**
   * Type of messenger error.
   *
   * @var string
   */
  const STATUS_FIELD = 'field-status';

  /**
   * Type of messenger error.
   *
   * @var string
   */
  const TYPE_ERROR_MESSAGE = 'content-import-message-error';

  /**
   * Type of messenger success.
   *
   * @var string
   */
  const TYPE_SUCCESS_MESSAGE = 'content-import-message-success';

  /**
   * Type of messenger link holder.
   */
  const TYPE_VALIDATED_SHEET_LINK = 'content-import-validated-sheet-link';

  /**
   * The connection database.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The batch operation service.
   *
   * @var \Drupal\umio_batch_import\Service\BatchOperationService
   */
  protected $batchOperationService;

  /**
   * The file system.
   *
   * @var \Drupal\umio_batch_import\Service\PhpOfficeService
   */
  protected $phpOfficeService;

  /**
   * Service to the tid of taxonomy terms.
   *
   * @var \Drupal\umio_batch_import\Service\BatchImportTaxonomyService
   */
  protected $taxonomyService;

  /**
   * Define the FormValidator.
   *
   * @var \Drupal\umio_user\Service\UserService
   */
  protected $userService;

  /**
   * Get a array with the sheet template header.
   *
   * @return array
   *   Array with the sheet template header.
   */
  abstract protected function getTemplateHeader(): array;

  /**
   * Get content type name.
   *
   * @param \Drupal\Core\Entity\EntityConstraintViolationListInterface $violations
   *   List of violations errors.
   *
   * @return \Drupal\Core\Entity\EntityConstraintViolationListInterface
   *   Returns the content type name.
   */
  abstract protected function customViolations(EntityConstraintViolationListInterface $violations): EntityConstraintViolationListInterface;

  /**
   * Get field machine name by the column on the spreadsheet.
   *
   * @param string $column
   *   The current column of the spreadsheet.
   *
   * @return string|null
   *   Returns the field machine name of the current column.
   */
  abstract protected function getFieldMachineNameByColumn(string $column): ?string;

  /**
   * Process the value of the column and changed based on the type.
   *
   * @param string $field
   *   The current column of the spreadsheet.
   * @param string $headerColumn
   *   The current header of the column.
   * @param string|null $value
   *   The current value of the column.
   *
   * @return string|null
   *   Value of the column change based on the type.
   */
  abstract protected function transformData(string $field, string $headerColumn, ?string $value): ?string;

  /**
   * Create the entity with the data.
   *
   * @param array $data
   *   Data to set to the entity.
   *
   * @return \Drupal\Core\Entity\EditorialContentEntityBase
   *   The entity created.
   */
  abstract protected function createEntity(array $data): EditorialContentEntityBase;

  /**
   * The construct method.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   The connection database.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\umio_batch_import\Service\BatchOperationService $batchOperationService
   *   The batch operation service.
   * @param \Drupal\umio_batch_import\Service\PhpOfficeService $phpOfficeService
   *   The php office helper service.
   * @param \Drupal\umio_batch_import\Service\BatchImportTaxonomyService $taxonomyService
   *   The taxonomy helper service.
   * @param \Drupal\umio_user\Service\UserService $userService
   *   A service for user functions.
   */
  final public function __construct(
    Connection $connection,
    EntityTypeManagerInterface $entityTypeManager,
    BatchOperationService $batchOperationService,
    PhpOfficeService $phpOfficeService,
    BatchImportTaxonomyService $taxonomyService,
    UserService $userService
  ) {
    $this->connection = $connection;
    $this->entityTypeManager = $entityTypeManager;
    $this->batchOperationService = $batchOperationService;
    $this->phpOfficeService = $phpOfficeService;
    $this->taxonomyService = $taxonomyService;
    $this->userService = $userService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): AbstractBatchImportForm {
    return new static(
      $container->get('database'),
      $container->get('entity_type.manager'),
      $container->get('umio_batch_import.batch_operation'),
      $container->get('umio_batch_import.phpoffice'),
      $container->get('umio_batch_import.taxonomy'),
      $container->get('umio_user.user_service'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form = [
      '#attributes' => [
        'enctype' => 'multipart/form-data',
        'class' => [
          'batch-import-form',
        ],
      ],
      '#disable_inline_form_errors' => TRUE,
    ];

    $form = $this->generateStatusMarkupField($form);

    $form['file'] = [
      '#type' => 'managed_file',
      '#name' => 'file',
      '#title' => $this->t('Sheet to import'),
      '#size' => 20,
      '#upload_validators' => [
        'file_validate_extensions' => ['xlsx'],
      ],
      '#upload_location' => self::DIRECTORY_UPLOAD,
      '#required' => TRUE,
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Send sheet'),
      '#validate' => ['::validateHeaderSheet'],
    ];

    return $form;
  }

  /**
   * Validating the header of the file sheet.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function validateHeaderSheet(array &$form, FormStateInterface $form_state): void {
    if (!$form_state->getValue('file')) {
      $form_state->setErrorByName('file', $this->t('File required.'));
    }
    else {
      /** @var \PhpOffice\PhpSpreadsheet\Spreadsheet $spreadsheet */
      $spreadsheet = $this->phpOfficeService->loadSpreadsheet($form_state);
      $sheet = $spreadsheet->getActiveSheet();
      $header = $this->phpOfficeService->getHeaderSpreeadSheet($sheet);
      if ($header !== $this->getTemplateHeader()) {
        $form_state->setErrorByName('file', $this->t('Please do not change the sheet template!'));
      }

      $this->checkEmptyFile($sheet, $form_state);
    }
  }

  /**
   * Check if the spreeadsheet is empty.
   *
   * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $spreadsheet
   *   The current array spreadsheet.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  private function checkEmptyFile(Worksheet $spreadsheet, FormStateInterface $form_state): void {
    $startRow = 2;
    foreach ($spreadsheet->getRowIterator($startRow, $startRow) as $row) {
      $cellIterator = $row->getCellIterator();
      $cellIterator->setIterateOnlyExistingCells(TRUE);
      $hasValue = FALSE;
      foreach ($cellIterator as $cell) {
        $value = $cell->getValue();
        if ($value !== NULL && $value !== '' && $value !== '=FALSE()' && $value !== FALSE) {
          $hasValue = TRUE;
          break;
        }
      }
      if (!$hasValue) {
        $form_state->setErrorByName('file', $this->t('Empty file!'));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $transaction = $this->connection->startTransaction();
    try {
      /** @var \PhpOffice\PhpSpreadsheet\Spreadsheet $spreadsheet */
      $spreadsheet = $this->phpOfficeService->loadSpreadsheet($form_state);
      $sheet = $spreadsheet->getActiveSheet();
      $header = $this->phpOfficeService->getHeaderSpreeadSheet($sheet);

      $savedEntities = 0;
      $errorEntities = 0;

      $lastRow = $sheet->getHighestDataRow();
      $rowsToRemove = [];
      $entities = [];
      foreach ($sheet->getRowIterator(2, $lastRow) as $key => $row) {
        $fields = $this->getFieldsValuesInRow($row, $header);
        if (!empty($fields)) {
          $address['administrative_area'] = $fields["field_vacancy_address"][0] ?? NULL;
          $address['locality'] = $fields["field_vacancy_address"][1] ?? NULL;
          $fields['field_vacancy_address'] = AddressService::getStateAndLocalityField($address['administrative_area'], $address['locality']);
          $fields['field_vacancy_company'] = $this->userService->getCurrentUserMainCompany();
          $entity = $this->createEntity($fields);
          $violations = $entity->validate();
          $violations = $this->customViolations($violations);
          if (count($violations) === 0) {
            $entities[] = $entity;
            $savedEntities++;
            $rowsToRemove[] = $key;
          }
          else {
            $errorEntities++;
          }
        }
      }

      $this->batchOperationService->batchOperation(
        $entities,
        [
          $this,
          'saveData',
        ]
      );

      if ($savedEntities) {
        $this->messenger()->addMessage(
          $this->t('Excel File Imported Successfully</br><strong>@number</strong> entries saved.', [
            '@number' => $savedEntities,
          ]),
          self::TYPE_SUCCESS_MESSAGE,
        );

        $deletedRowCount = 0;
        foreach ($rowsToRemove as $value) {
          $row = $value - $deletedRowCount;
          $sheet->removeRow($row, 1);
          $deletedRowCount++;
        }
      }
      if ($errorEntities) {
        if ($errorEntities === 1) {
          $msg = $this->t('<strong>@number</strong> item apresentou erro. <br>Baixe a planilha com esse item clicando no botão ao lado.', [
            '@number' => $errorEntities,
          ]);
        }
        else {
          $msg = $this->t('<strong>@number</strong> itens apresentaram erros. <br>Baixe a planilha com a lista desses itens clicando no botão ao lado.', [
            '@number' => $errorEntities,
          ]);
        }
        $this->messenger()->addMessage(
          $msg,
          self::TYPE_ERROR_MESSAGE,
        );
        $this->phpOfficeService->createErrorSpreadsheet($spreadsheet);
      }

      $this->phpOfficeService->deleteSpreadSheet($form_state);
    }
    catch (\Exception $e) {
      $transaction->rollBack();
      $this->logger('type')->error($e->getMessage());
      $this->phpOfficeService->deleteSpreadSheet($form_state);
      $this->messenger()->addError($this->t('An unexpected error ocurred. Please try again later.'));
    }
  }

  /**
   * Function to return the values of the fields in the current row.
   *
   * @param \PhpOffice\PhpSpreadsheet\Worksheet\Row $row
   *   The current row of the spreadsheet.
   * @param array $header
   *   Array the header of the spreadsheet.
   *
   * @return array
   *   Array with the values of the fields in the current row.
   */
  private function getFieldsValuesInRow(Row $row, array $header): array {
    $cellIterator = $row->getCellIterator();
    $cellIterator->setIterateOnlyExistingCells(TRUE);
    $fields = [];
    foreach ($cellIterator as $cellKey => $cell) {
      $field = $this->getFieldMachineNameByColumn($cellKey);
      if ($field) {
        $value = $cell->getValue();
        $value = $this->transformData($field, $header[$cellKey], $value);
        if ($value !== NULL) {
          if (isset($fields[$field])) {
            if (!is_array($fields[$field])) {
              $oldValue = $fields[$field];
              $fields[$field] = [$oldValue];
            }
            $fields[$field][] = $value;
          }
          else {
            $fields[$field] = $value;
          }
        }
      }
    }
    return $fields;
  }

  /**
   * Function to execute by batch to save the node.
   *
   * @param \Drupal\Core\Entity\EditorialContentEntityBase $entity
   *   The entity to be saved.
   * @param array $context
   *   Context of the batch.
   */
  public function saveData(EditorialContentEntityBase $entity, array &$context): void {
    $entity->save();
    $context['results'][] = $entity->label();
    $context['message'] = $this->t('Created @title', ['@title' => $entity->label()]);
  }

}
