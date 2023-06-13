<?php

namespace Drupal\app_step_form\Validator;

/**
 * Class ValidatorRegex.
 *
 * @package Drupal\app_step_form\Validator
 */
class ValidatorRegex extends BaseValidator {

  protected string $pattern;

  /**
   * ValidatorRegex constructor.
   *
   * @param string $error_message
   *   Error message.
   * @param string $pattern
   *   Regex pattern.
   */
  public function __construct($error_message, string $pattern) {
    parent::__construct($error_message);
    $this->pattern = $pattern;
  }

  /**
   * {@inheritdoc}
   */
  public function validates($value): bool|int {
    return preg_match($this->pattern, $value);
  }

}
