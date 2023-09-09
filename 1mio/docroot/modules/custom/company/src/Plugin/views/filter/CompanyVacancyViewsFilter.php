<?php

namespace Drupal\company\Plugin\views\filter;

/**
 * Filtering for company field in vacancy content type.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("company_vacancy_filter")
 */
class CompanyVacancyViewsFilter extends AbstractCompanyViewsFilter {

  /**
   * {@inheritdoc}
   */
  protected function getTableName(): string {
    return 'node__field_vacancy_company';
  }

  /**
   * {@inheritdoc}
   */
  protected function getFieldName(): string {
    return 'field_vacancy_company_target_id';
  }

}
