<?php

namespace Drupal\app_step_form\Validator;

/**
 * Interface ValidatorInterface.
 *
 * @package Drupal\app_step_form\Validator
 */
interface ValidatorInterface {

  /**
   * Returns bool indicating if validation is ok.
   */
  public function validates($value);

  /**
   * Returns error message.
   */
  public function getErrorMessage();

}
