<?php

namespace Drupal\umio_helpers\Service;

use CommerceGuys\Addressing\AddressFormat\AddressField;
use CommerceGuys\Addressing\AddressFormat\FieldOverride;
use Drupal\Core\Form\FormStateInterface;

/**
 * Define the AddressService class.
 */
class AddressService {

  /**
   * Function to return a state and locality address field.
   *
   * @param string|null $state
   *   The state default.
   * @param string|null $locality
   *   The localtiy default.
   *
   * @return array
   *   The address field.
   */
  public static function getStateAndLocalityField(?string $state = NULL, ?string $locality = NULL): array {
    return [
      '#type' => 'address',
      '#default_value' => [
        'country_code' => 'BR',
        'administrative_area' => $state,
        'locality' => $locality,
      ],
      '#field_overrides' => [
        AddressField::ADMINISTRATIVE_AREA => FieldOverride::REQUIRED,
        AddressField::LOCALITY => FieldOverride::REQUIRED,
        AddressField::DEPENDENT_LOCALITY => FieldOverride::HIDDEN,
        AddressField::POSTAL_CODE => FieldOverride::HIDDEN,
        AddressField::SORTING_CODE => FieldOverride::HIDDEN,
        AddressField::ADDRESS_LINE1 => FieldOverride::HIDDEN,
        AddressField::ADDRESS_LINE2 => FieldOverride::HIDDEN,
        AddressField::ORGANIZATION => FieldOverride::HIDDEN,
        AddressField::GIVEN_NAME => FieldOverride::HIDDEN,
        AddressField::ADDITIONAL_NAME => FieldOverride::HIDDEN,
        AddressField::FAMILY_NAME => FieldOverride::HIDDEN,
      ],
      '#available_countries' => ['BR'],
      '#after_build' => ['Drupal\umio_helpers\Service\AddressService::overrideDefaults'],
    ];
  }

  /**
   * Function to return a state and locality address field.
   *
   * @return array
   *   The address field.
   */
  public static function getFullEmptyAddressField(): array {
    return [
      '#type' => 'address',
      '#default_value' => [
        'country_code' => 'BR',
      ],
      '#field_overrides' => [
        AddressField::ADMINISTRATIVE_AREA => FieldOverride::REQUIRED,
        AddressField::LOCALITY => FieldOverride::REQUIRED,
        AddressField::DEPENDENT_LOCALITY => FieldOverride::HIDDEN,
        AddressField::POSTAL_CODE => FieldOverride::HIDDEN,
        AddressField::SORTING_CODE => FieldOverride::HIDDEN,
        AddressField::ADDRESS_LINE1 => FieldOverride::HIDDEN,
        AddressField::ADDRESS_LINE2 => FieldOverride::HIDDEN,
        AddressField::ORGANIZATION => FieldOverride::HIDDEN,
        AddressField::GIVEN_NAME => FieldOverride::HIDDEN,
        AddressField::ADDITIONAL_NAME => FieldOverride::HIDDEN,
        AddressField::FAMILY_NAME => FieldOverride::HIDDEN,
      ],
      '#available_countries' => ['BR'],
      '#after_build' => [
        'Drupal\umio_helpers\Service\AddressService::overrideDefaults',
        'Drupal\umio_helpers\Service\AddressService::overrideWeight',
      ],
    ];
  }

  /**
   * Function to return a state and locality address field.
   *
   * @param array $element
   *   The address field.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The state of the form.
   *
   * @return array
   *   The address field.
   */
  public static function overrideDefaults(array $element, FormStateInterface $form_state): array {
    $element['administrative_area']['#prefix'] = '<div class="row"><div class="col-6">';
    $element['administrative_area']['#suffix'] = '</div>';
    $element['locality']['#prefix'] = '<div class="col-6">';
    $element['locality']['#suffix'] = '</div></div>';

    // Validated so when change state with locality selected don't throw error.
    $element['locality']['#validated'] = TRUE;

    return $element;
  }

  /**
   * Function to return a state and locality address field.
   *
   * @param array $element
   *   The address field.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The state of the form.
   *
   * @return array
   *   The address field.
   */
  public static function overrideWeight(array $element, FormStateInterface $form_state): array {
    $element['address_line1']['#weight'] = 0;
    $element['dependent_locality']['#weight'] = 1;
    $element['postal_code']['#weight'] = 2;
    $element['administrative_area']['#weight'] = 3;
    $element['locality']['#weight'] = 4;

    return $element;
  }

}
