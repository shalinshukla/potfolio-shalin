<?php

namespace Drupal\app_step_form\Step;

use Drupal\views\Views;

/**
 * Class StepFinalize.
 *
 * @package Drupal\app_step_form\Step
 */
class StepFinalize extends BaseStep {

  /**
   * {@inheritdoc}
   */
  protected function setStep(): int {
    return StepsEnum::STEP_FINALIZE;
  }

  /**
   * {@inheritdoc}
   */
  public function getButtons(): array {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function buildStepFormElements(): array {

    $view = Views::getView('active_angular_application');
    $form['completed'] = [
      '#markup' => t('Block is generated successfully!'),
    ];

    $form['app_view'] = $view->buildRenderable('block_1');

    return $form;
  }

}
