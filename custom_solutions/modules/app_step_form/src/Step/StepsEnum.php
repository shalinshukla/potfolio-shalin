<?php

namespace Drupal\app_step_form\Step;

/**
 * Class StepsEnum.
 *
 * @package Drupal\app_step_form\Step
 */
abstract class StepsEnum {

  /**
   * Steps used in form.
   */
  const STEP_ONE = 1;
  const STEP_TWO = 2;
  const STEP_THREE = 3;
  const STEP_FINALIZE = 6;

  /**
   * Return steps associative array.
   *
   * @return array
   *   Associative array of steps.
   */
  public static function toArray(): array {
    return [
      self::STEP_ONE => 'step-one',
      self::STEP_TWO => 'step-two',
      self::STEP_THREE => 'step-three',
      self::STEP_FINALIZE => 'step-finalize',
    ];
  }

  /**
   * Map steps to its class.
   *
   * @param int $step
   *   Step number.
   *
   * @return bool|string Return true if existed.
   *   Return true if existed.
   */
  public static function map(int $step): bool|string {
    $map = [
      self::STEP_ONE => 'Drupal\\app_step_form\\Step\\StepOne',
      self::STEP_TWO => 'Drupal\\app_step_form\\Step\\StepTwo',
      self::STEP_THREE => 'Drupal\\app_step_form\\Step\\StepThree',
      self::STEP_FINALIZE => 'Drupal\\app_step_form\\Step\\StepFinalize',
    ];

    return $map[$step] ?? FALSE;
  }

}
