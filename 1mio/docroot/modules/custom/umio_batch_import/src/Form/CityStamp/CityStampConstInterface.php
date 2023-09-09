<?php

namespace Drupal\umio_batch_import\Form\CityStamp;

/**
 * Interface to handle const of city stamp import.
 */
interface CityStampConstInterface {

  /**
   * Array with the respective field in the column of the template spreadsheet.
   *
   * @var array
   */
  const FIELDS_SPREEADSHEET = [
    'A' => 'field_stamp_unicef_ibge',
    'B' => 'state',
    'C' => 'name',
  ];

  /**
   * Array with the header of the template spreadsheet.
   *
   * @var array
   */
  const HEADER_SPREEADSHEET = [
    'A' => 'IBGE',
    'B' => 'Estado',
    'C' => 'Cidade',
  ];

  /**
   * List of all brazilian states.
   *
   * @var array
   */
  const LIST_STATES = [
    'AC' => 'Acre',
    'AL' => 'Alagoas',
    'AP' => 'Amapá',
    'AM' => 'Amazonas',
    'BA' => 'Bahia',
    'CE' => 'Ceará',
    'DF' => 'Distrito Federal',
    'ES' => 'Espírito Santo',
    'GO' => 'Goiás',
    'MA' => 'Maranhão',
    'MT' => 'Mato Grosso',
    'MS' => 'Mato Grosso do Sul',
    'MG' => 'Minas Gerais',
    'PA' => 'Pará',
    'PB' => 'Paraíba',
    'PR' => 'Paraná',
    'PE' => 'Pernambuco',
    'PI' => 'Piauí',
    'RJ' => 'Rio de Janeiro',
    'RN' => 'Rio Grande do Norte',
    'RS' => 'Rio Grande do Sul',
    'RO' => 'Rondônia',
    'RR' => 'Roraima',
    'SC' => 'Santa Catarina',
    'SP' => 'São Paulo',
    'SE' => 'Sergipe',
    'TO' => 'Tocantins',
  ];

  /**
   * Array with the region strutured by states.
   *
   * @var array
   */
  const REGION_STATES = [
    [
      'name' => 'Amazônia Legal',
      'parent' => [],
      'states' => [
        'AC',
        'AP',
        'AM',
        'MA',
        'MT',
        'PA',
        'RO',
        'RR',
        'TO',
      ],
    ],
    [
      'name' => 'Região do Semiárido Brasileiro',
      'parent' => [],
      'states' => [
        'AL',
        'BA',
        'CE',
        'MG',
        'PB',
        'PE',
        'PI',
        'RN',
        'SE',
      ],
    ],
  ];

}
