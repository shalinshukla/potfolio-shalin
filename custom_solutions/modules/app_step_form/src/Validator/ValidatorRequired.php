<?php

namespace Drupal\app_step_form\Validator;

/**
 * Class ValidatorRequired.
 *
 * @package Drupal\app_step_form\Validator
 */
class ValidatorRequired extends BaseValidator {

  /**
   * {@inheritdoc}
   */
  public function validates($value): bool {
    return is_array($value) ? !empty(array_filter($value)) : !empty($value);
  }

}
