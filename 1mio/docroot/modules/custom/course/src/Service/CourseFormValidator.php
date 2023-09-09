<?php

namespace Drupal\course\Service;

use Drupal\Core\Form\FormStateInterface;
use Drupal\umio_helpers\Service\FormValidator;

/**
 * Class to validate the course form.
 */
final class CourseFormValidator {

  /**
   * Token manager.
   *
   * @var \Drupal\umio_helpers\Service\FormValidator
   */
  protected $formValidator;

  /**
   * Current Date.
   *
   * @var string
   */
  private $currentDate;

  /**
   * Constructs a \Drupal\course\Service\CourseFormValidator object.
   *
   * @param \Drupal\umio_helpers\Service\FormValidator $formValidator
   *   Helper class to validate input.
   */
  public function __construct(FormValidator $formValidator) {
    $this->formValidator = $formValidator;
    $this->currentDate = date("Y-m-d");
  }

  /**
   * Validating the course.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function validateCourse(FormStateInterface $form_state): void {
    $this->validateCourseExpirationDate($form_state);
    $this->validateCourseDate($form_state);
    $this->validateCourseTime($form_state);
    $this->validateCourseDuration($form_state);
  }

  /**
   * Validating the dates of the course.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function validateCourseDate(FormStateInterface $form_state): void {
    $startDate = $form_state->getValue('field_course_start_date')[0]['value'];
    $endDate = $form_state->getValue('field_course_end_date')[0]['value'];

    if (gettype($startDate) == 'object' && gettype($endDate) == 'object') {
      if ($endDate < $startDate) {
        $form_state->setErrorByName('field_course_end_date', t('Ending date must be after starting date.'));
      }
      if ($endDate < $this->currentDate) {
        $form_state->setErrorByName('field_course_end_date', t('Ending date must be after current date.'));
      }
    }
  }

  /**
   * Validates if ending time is after the start time.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function validateCourseTime(FormStateInterface $form_state): void {
    $startTime = $form_state->getValue('field_course_start_time')[0]['value'];
    $endTime = $form_state->getValue('field_course_end_time')[0]['value'];

    if ($endTime < $startTime) {
      $form_state->setErrorByName('field_course_end_time', t('Ending time must be after starting time.'));
    }
  }

  /**
   * Validates if course expiration date is after present date.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function validateCourseExpirationDate(FormStateInterface $form_state):void {
    $date = $form_state->getValue('field_course_expiration_day')[0]['value'];

    if (gettype($date) == 'object') {
      $expirationDate = $date->format('Y-m-d');
      if ($expirationDate < $this->currentDate) {
        $form_state->setErrorByName('field_course_expiration_day', t('Expiration date must be after current date.'));
      }
    }

  }

  /**
   * Validates if course duration is positive.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function validateCourseDuration(FormStateInterface $form_state):void {
    $courseDuration = $form_state->getValue('field_course_duration')[0]['value'];

    if ($courseDuration < 1) {
      $form_state->setErrorByName('field_course_duration', t('Course duration must be postive.'));
    }
  }

}
