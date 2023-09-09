<?php

namespace Drupal\umio_helpers;

use Drupal\Core\Render\Element\RenderCallbackInterface;

/**
 * Provides a trusted callback to alter select2 default configuration.
 */
class PreRenderSelect2 implements RenderCallbackInterface {

  /**
   * Function to change select2 element field default configurations.
   *
   * @var array $element
   *   Select2 element field.
   *
   * @return array
   *   Select2 element with new configurations.
   */
  public static function preRender(array $element): array {
    $select2Configs = $element['#attributes']['data-select2-config'];
    $select2Configs = json_decode($select2Configs, TRUE);
    $select2Configs['theme'] = 'bootstrap-5';
    $select2Configs['allowClear'] = TRUE;
    $select2Configs['language'] = 'pt-BR';

    $element['#attributes']['data-select2-config'] = json_encode($select2Configs);

    return $element;
  }

}
