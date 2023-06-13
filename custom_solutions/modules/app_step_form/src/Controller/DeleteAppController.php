<?php

namespace Drupal\app_step_form\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Url;

/**
 * Provides route responses for the Example module.
 */
class DeleteAppController extends ControllerBase {

  /**
   * Returns a simple page.
   *
   * @param string $block_uuid
   * @param string $file_unique_id
   * @return RedirectResponse|array A simple renderable array.
   *   A simple renderable array.
   */
  public function delete_app(string $block_uuid = '', string $file_unique_id = '', string $directory_type = ''): RedirectResponse|array
  {

    if(!empty($block_uuid) && !empty($file_unique_id) && !empty($directory_type)) {
      $data = \Drupal::service('app_step_form.delete_active_app')->deleteApp($block_uuid, $file_unique_id, $directory_type);
    }

    $url = Url::fromRoute('app_step_form.page');
    return new RedirectResponse($url->toString());
  }

}
