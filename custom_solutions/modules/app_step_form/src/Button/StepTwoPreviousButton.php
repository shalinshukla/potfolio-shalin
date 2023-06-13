<?php

namespace Drupal\app_step_form\Button;

use Drupal\app_step_form\Step\StepsEnum;

/**
 * Class StepTwoPreviousButton.
 *
 * @package Drupal\app_step_form\Button
 */
class StepTwoPreviousButton extends BaseButton {

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
      '#goto_step' => StepsEnum::STEP_ONE,
      '#skip_validation' => TRUE,
    ];
  }

}
