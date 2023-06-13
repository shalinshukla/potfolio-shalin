<?php

namespace Drupal\app_step_form\Manager;

use Drupal\Core\TempStore\SharedTempStoreFactory;
use Drupal\app_step_form\Step\StepInterface;
use Drupal\app_step_form\Step\StepsEnum;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class StepManager.
 *
 * @package Drupal\app_step_form\Manager
 */
class StepManager {

  /**
   * Multi steps of the form.
   *
   * @var \Drupal\app_step_form\Step\StepInterface
   */
  protected $steps;


  /**
   * Add a step to the steps property.
   *
   * @param \Drupal\app_step_form\Step\StepInterface $step
   *   Step of the form.
   */
  public function addStep(StepInterface $step): void {
    $this->steps[$step->getStep()] = $step;
  }

  /**
   * Fetches step from steps property, If it doesn't exist, create step object.
   *
   * @param int $step_id
   *   Step ID.
   *
   * @return \Drupal\app_step_form\Step\StepInterface
   *   Return step object.
   */
  public function getStep(int $step_id): \Drupal\app_step_form\Step\StepInterface {
    if (isset($this->steps[$step_id])) {
      // If step was already initialized, use that step.
      // Chance is there are values stored on that step.
      $step = $this->steps[$step_id];
    }
    else {
      // Get class.
      $class = StepsEnum::map($step_id);
      // Init step.
      $step = new $class($this);
    }

    return $step;
  }

  /**
   * Get all steps.
   *
   * @return \Drupal\app_step_form\Step\StepInterface
   *   Steps.
   */
  public function getAllSteps(): StepInterface {
    return $this->steps;
  }

}
