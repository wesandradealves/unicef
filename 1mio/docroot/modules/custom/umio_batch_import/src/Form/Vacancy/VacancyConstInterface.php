<?php

namespace Drupal\umio_batch_import\Form\Vacancy;

/**
 * Interface to save vacancy types of fields.
 */
interface VacancyConstInterface {

  /**
   * Array with the respective field in the column of the template spreadsheet.
   *
   * @var array
   */
  const FIELDS_SPREEADSHEET = [
    'A' => 'title',
    'B' => 'field_vacancy_type',
    'C' => 'field_vacancy_job_model',
    'D' => 'field_vacancy_quantity',
    'E' => 'field_vacancy_closing_date',
    'F' => 'field_vacancy_activities',
    'G' => 'field_vacancy_salary_options',
    'H' => 'field_vacancy_salary',
    'I' => 'field_vacancy_salary_min',
    'J' => 'field_vacancy_salary_max',
    'K' => 'field_vacancy_subscription_url',
    'L' => 'field_vacancy_state',
    'M' => 'field_vacancy_city',
    'N' => 'field_vacancy_skills_match',
    'O' => 'field_vacancy_skills_match',
    'P' => 'field_vacancy_skills_match',
    'Q' => 'field_vacancy_skills_match',
    'R' => 'field_vacancy_benefits',
    'S' => 'field_vacancy_benefits',
    'T' => 'field_vacancy_benefits',
    'U' => 'field_vacancy_benefits',
    'V' => 'field_vacancy_benefits',
    'W' => 'field_vacancy_benefits',
    'X' => 'field_vacancy_priority_profiles',
    'Y' => 'field_vacancy_priority_profiles',
    'Z' => 'field_vacancy_priority_profiles',
    'AA' => 'field_vacancy_priority_profiles',
    'AB' => 'field_vacancy_priority_profiles',
    'AC' => 'field_vacancy_priority_profiles',
    'AD' => 'field_vacancy_priority_profiles',
    'AE' => 'field_vacancy_priority_profiles',
    'AF' => 'field_vacancy_priority_profiles',
    'AG' => 'field_vacancy_priority_profiles',
    'AH' => 'field_vacancy_priority_profiles',
  ];

  /**
   * Array with the header of the template spreadsheet.
   *
   * @var array
   */
  const HEADER_SPREEADSHEET = [
    'A' => 'Nome da vaga',
    'B' => 'Tipo de vaga',
    'C' => 'Modelo de trabalho',
    'D' => 'Quantidade de vagas',
    'E' => 'Quando encerram as inscrições',
    'F' => 'Atividades',
    'G' => 'Modelo de Salário',
    'H' => 'Salário',
    'I' => 'Salário Mínimo',
    'J' => 'Salário Máximo',
    'K' => 'Url de inscrição',
    'L' => 'Estado',
    'M' => 'Cidade',
    'N' => 'Determinação e criatividade',
    'O' => 'Liderança e colaboração',
    'P' => 'Proatividade e comunicação',
    'Q' => 'Aprendizagem e adaptabilidade',
    'R' => 'Vale alimentação',
    'S' => 'Vale refeição',
    'T' => 'Vale transporte',
    'U' => 'Plano odontólogico',
    'V' => 'Convênio médico',
    'W' => 'Licença maternidade',
    'X' => 'Adolescentes e jovens com deficiência',
    'Y' => 'Adolescentes e Jovens Mães',
    'Z' => 'Egressos do sistema socioeducativo e adolescentes e jovens cumprindo medidas socioeducativas em meio aberto',
    'AA' => 'Equidade Étnico Racial',
    'AB' => 'LGBTIA+',
    'AC' => 'Localização (adolescentes e jovens moradores de periferias urbanas e zonas rurais)',
    'AD' => 'Meninas nos mercados de Ciência, Tecnologia, Engenharia e afins',
    'AE' => 'Migrantes',
    'AF' => 'Populações originárias (indígenas, quilombolas e ribeirinhos)',
    'AG' => 'Renda (adolescentes e jovens sem renda ou renda per capita familiar de até R$ 150 mensais)',
    'AH' => 'Vítimas de trabalho infantil',
  ];

  /**
   * Allowed types of field field_vacancy_type.
   *
   * @var array
   */
  const FIELD_VACANCY_TYPE = [
    'young-apprentice'  => 'Jovem Aprendiz',
    'trainee'           => 'Trainee',
    'CLT'               => 'CLT',
    'temporary-clt'     => 'CLT Temporário',
    'internship'        => 'Estágio',
    'pj'                => 'PJ',
  ];

  /**
   * Allowed types of field field_vacancy_job_model.
   *
   * @var array
   */
  const FIELD_VACANCY_JOB_MODEL = [
    'present'   => 'Presencial',
    'remote'    => 'Remoto',
    'hybrid'    => 'Híbrido',
  ];

  /**
   * Allowed types of field field_vacancy_salary_options.
   *
   * @var array
   */
  const FIELD_VACANCY_SALARY_OPTIONS = [
    'unique'   => 'Valor único',
    'min_max'  => 'Valor mínimo e máximo',
  ];

}
