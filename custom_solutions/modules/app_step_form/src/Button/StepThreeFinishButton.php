<?php

namespace Drupal\app_step_form\Button;

use Drupal\app_step_form\Step\StepsEnum;

/**
 * Class StepThreeFinishButton.
 *
 * @package Drupal\app_step_form\Button
 */
class StepThreeFinishButton extends BaseButton {

  /**
   * {@inheritdoc}
   */
  public function getKey() {
    return 'finish';
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#type' => 'submit',
      '#value' => t('Generate Block!'),
      '#goto_step' => StepsEnum::STEP_FINALIZE,
      '#submit_handler' => 'submitValues',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getSubmitHandler() {
    return 'submitIntake';
  }

}
