<?php

namespace Drupal\app_step_form\Step;

/**
 * Class BaseStep.
 *
 * @package Drupal\app_step_form\Step
 */
abstract class BaseStep implements StepInterface {

  /**
   * Multi steps of the form.
   *
   * @var StepInterface
   */
  protected $step;

  /**
   * Values of element.
   *
   * @var array
   */
  protected $values;

  /**
   * BaseStep constructor.
   */
  public function __construct() {
    $this->step = $this->setStep();
  }

  /**
   * {@inheritdoc}
   */
  public function getStep(): int {
    return $this->step;
  }

  /**
   * {@inheritdoc}
   */
  public function isLastStep(): bool {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function setValues($values) {
    $this->values = $values;
  }

  /**
   * {@inheritdoc}
   */
  public function getValues(){
    return $this->values;
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldNames(): array {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldsValidators(): array {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  abstract protected function setStep();

}
