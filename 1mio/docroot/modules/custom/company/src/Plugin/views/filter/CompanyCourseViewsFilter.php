<?php

namespace Drupal\company\Plugin\views\filter;

/**
 * Filtering for company field in course content type.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("company_course_filter")
 */
class CompanyCourseViewsFilter extends AbstractCompanyViewsFilter {

  /**
   * {@inheritdoc}
   */
  protected function getTableName(): string {
    return 'node__field_course_institution';
  }

  /**
   * {@inheritdoc}
   */
  protected function getFieldName(): string {
    return 'field_course_institution_target_id';
  }

}
