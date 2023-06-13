<?php

namespace Drupal\app_step_form\Button;

use Drupal\app_step_form\Step\StepsEnum;

/**
 * Class StepTwoNextButton.
 *
 * @package Drupal\app_step_form\Button
 */
class StepTwoNextButton extends BaseButton {

  /**
   * {@inheritdoc}
   */
  public function getKey() {
    return 'next';
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#type' => 'submit',
      '#value' => t('Next'),
      '#goto_step' => StepsEnum::STEP_THREE,
    ];
  }

}
