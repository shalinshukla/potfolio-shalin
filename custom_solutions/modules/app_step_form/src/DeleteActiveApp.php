<?php

namespace Drupal\app_step_form;


/**
 * Class CustomService
 * @package Drupal\mymodule\Services
 */
class DeleteActiveApp {

  /**
   * @param string $UUID
   * @param string $fileID
   * @return true|void
   */
  public function deleteApp(string $UUID = '',
                            string $fileID = '',
                            string $directoryType = '')
  {

    if($directoryType == 'private') {
      $module_handler = \Drupal::service('module_handler');
      $filesDirectory = $module_handler->getModule('app_step_form')->getPath() . '/';
    } else {
      $filesDirectory = 'public://';
    }
    $this->rrmdir($filesDirectory . 'angular_component/angular_component_' . $fileID);
    $block_content = \Drupal::service('entity.repository')->loadEntityByUuid('block_content', $UUID);
    $block_content->delete();
  }

  public function rrmdir($dir) {
    if (is_dir($dir)) {
      $objects = scandir($dir);
      foreach ($objects as $object) {
        if ($object != "." && $object != "..") {
          if (is_dir($dir. DIRECTORY_SEPARATOR .$object) && !is_link($dir."/".$object))
            $this->rrmdir($dir. DIRECTORY_SEPARATOR .$object);
          else
            unlink($dir. DIRECTORY_SEPARATOR .$object);
        }
      }
      rmdir($dir);
    }
  }
}
