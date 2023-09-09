<?php

namespace Drupal\umio_helpers\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'range' widget.
 *
 * @FieldWidget(
 *   id = "range_custom",
 *   label = @Translation("Range"),
 *   field_types = {
 *     "integer",
 *     "decimal",
 *     "float"
 *   }
 * )
 */
class RangeWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $field_settings = $this->getFieldSettings();
    $value = $items[$delta]->value ?? '';

    $element += [
      '#type' => 'range_custom',
      '#default_value' => $value,
      '#min' => $field_settings['min'],
      '#max' => $field_settings['max'],
    ];
    return ['value' => $element];
  }

}
