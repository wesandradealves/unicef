<?php

namespace Drupal\umio_helpers\Element;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element\Range;

/**
 * Provides a slider for input of a number within a specific range.
 *
 * Wraps rangeslider.js around HTML5 range input element.
 *
 * Properties:
 * - #min: Minimum value (defaults to 0).
 * - #max: Maximum value (defaults to 100).
 * Refer to \Drupal\Core\Render\Element\Number for additional properties.
 *
 * Usage example:
 * @code
 * $form['quantity'] = [
 *   '#type' => 'range_custom',
 *   '#title' => $this->t('Quantity'),
 * ];
 * @endcode
 *
 * @see \Drupal\Core\Render\Element\Range
 *
 * @FormElement("range_custom")
 */
class RangeElementCustom extends Range {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $info = parent::getInfo();
    $class = static::class;
    return [
      '#min' => 0,
      '#max' => 100,
      '#process' => [
        [get_class($this), 'processRangeCustom'],
      ],
      '#pre_render' => [
        [$class, 'preRenderRange'],
      ],
      '#theme' => 'input__range',
    ] + $info;
  }

  /**
   * Processes a rangeslider form element.
   *
   * @var array $element
   * @var \Drupal\Core\Form\FormStateInterface $form_state
   * @var array $complete_form
   */
  public static function processRangeCustom(array &$element, FormStateInterface $form_state, array &$complete_form): array {
    $element['#attached']['library'][] = 'umio_helpers/umio_helpers.range';
    $element['#attributes']['class'][] = 'js-range-slider';
    $element['#attributes']['data-min'] = $element['#min'];
    $element['#attributes']['data-max'] = $element['#max'];

    return $element;
  }

}
