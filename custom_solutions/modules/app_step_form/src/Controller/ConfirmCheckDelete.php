<?php

namespace Drupal\app_step_form\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Url;

/**
 * Provides route responses for the Example module.
 */
class ConfirmCheckDelete extends ControllerBase {

  /**
   * Returns a simple page.
   *
   * @param string $block_uuid
   * @param string $file_unique_id
   * @return RedirectResponse|array A simple renderable array.
   *   A simple renderable array.
   */
  public function confirm_delete_app(string $block_uuid = '', string $file_unique_id = '', string $directory_type = ''): RedirectResponse|array
  {

    $markupHTML = '';
    if(!empty($block_uuid) && !empty($file_unique_id) && !empty($directory_type)) {
      $markupHTML = '<div id="delete_message">';
      $markupHTML .= '<h2>Are You Sure You Want To Delete This ?</h2>';
      $markupHTML .= '<a href="/angular/' . $block_uuid . '/' . $file_unique_id . '/' . $directory_type . '" class="button form-submit"> Delete </a>';
      $markupHTML .= '</div>';
    }

    return [
      '#markup' => $markupHTML,
    ];
  }

}
