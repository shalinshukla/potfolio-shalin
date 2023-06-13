<?php

namespace Drupal\app_step_form\Button;

/**
 * Class BaseButton.
 *
 * @package Drupal\app_step_form\Button
 */
abstract class BaseButton implements ButtonInterface {

  /**
   * {@inheritdoc}
   */
  public function ajaxify() {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function getSubmitHandler() {
    return FALSE;
  }

}
