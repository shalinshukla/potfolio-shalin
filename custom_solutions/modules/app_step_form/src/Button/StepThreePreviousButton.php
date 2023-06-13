<?php

namespace Drupal\app_step_form\Button;

use Drupal\app_step_form\Step\StepsEnum;

/**
 * Class StepThreePreviousButton.
 *
 * @package Drupal\app_step_form\Button
 */
class StepThreePreviousButton extends BaseButton {

  /**
   * {@inheritdoc}
   */
  public function getKey() {
    return 'previous';
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#type' => 'submit',
      '#value' => t('Previous'),
      '#goto_step' => StepsEnum::STEP_TWO,
      '#skip_validation' => TRUE,
    ];
  }

}
