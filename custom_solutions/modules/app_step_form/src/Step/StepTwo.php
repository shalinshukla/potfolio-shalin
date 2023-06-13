<?php

namespace Drupal\app_step_form\Step;

use Drupal\app_step_form\Button\StepTwoNextButton;
use Drupal\app_step_form\Button\StepTwoPreviousButton;
use Drupal\app_step_form\Validator\ValidatorRequired;
use Drupal\Core\Archiver\Zip;
use Drupal\Core\Archiver\ArchiverException;


/**
 * Class StepTwo.
 *
 * @package Drupal\app_step_form\Step
 */
class StepTwo extends BaseStep {


  /**
   * {@inheritdoc}
   */
  protected function setStep(): int {
    return StepsEnum::STEP_TWO;
  }


  /**
   * {@inheritdoc}
   */
  public function getButtons(): array {
    return [
      new StepTwoPreviousButton(),
      new StepTwoNextButton(),
    ];
  }

  /**
   * {@inheritdoc}
   * @throws \Drupal\Core\TempStore\TempStoreException
   */
  public function buildStepFormElements(): array {

    $store = \Drupal::service('tempstore.private')->get('app_step_form');
    $formState = $store->get('form_state_' . StepsEnum::STEP_ONE);
    $fieldId = $formState['angular_folder_zip'][0];
    $repository = $formState['public_or_private'];
    if($repository == 'private') {
      $module_handler = \Drupal::service('module_handler');
      $filesDirectory = $module_handler->getModule('app_step_form')->getPath() . '/';
    } else {
      $filesDirectory = 'public://';
    }
    if(!empty($fieldId)) {
      //if (!file_exists('public://angular_component/angular_component_' . $fieldId)) {
        /** @var \Drupal\Core\TempStore\PrivateTempStore $store */

        /** @var \Drupal\file\FileInterface|null $file*/
        $file = \Drupal::entityTypeManager()
          ->getStorage('file')
          ->load($formState['angular_folder_zip'][0]);
        $uri = $file->getFileUri();
        $stream_wrapper_manager = \Drupal::service('stream_wrapper_manager')->getViaUri($uri);
        $file_path = $stream_wrapper_manager->realpath();


        mkdir($filesDirectory . 'angular_component/angular_component_' . $fieldId, 0777, TRUE);
        $uploader = $filesDirectory . 'angular_component/angular_component_' . $fieldId;
        $totalFiles = [];
        try {
          $zip = new Zip($file_path);
          $zip->extract($uploader);
          $totalFiles = $zip->listContents();
          $file->delete();
        } catch (ArchiverException $exception) {
          watchdog_exception('app_step_form', $exception);
        }

        $previewFiles = [];
        foreach ($totalFiles as $key => $item) {
          if (str_contains($item , '.html')) {
            $previewFiles[$key] = $item;
          }
        }
        foreach ($totalFiles as $libraryFiles) {
          if (str_contains($libraryFiles , '.css') || str_contains($libraryFiles , '.js')) {
            $arrayOfFileElement = explode('/', $libraryFiles);
            $folderName = $arrayOfFileElement[0];
            $libraryFileNames[] = end($arrayOfFileElement);
          }
        }
        $store->set('preview_files', $previewFiles);
        $store->set('library_names', $libraryFileNames);
        $store->set('folder_name', $folderName);

      } else {
        $previewFiles = $store->get('preview_files');
      }
      $form['select_html_file'] = [
        '#type' => 'radios',
        '#title' => t('Select App root file'),
        '#options' => $previewFiles,
        '#default_value' => $this->getValues()['select_html_file'] ?? [],
        '#required' => TRUE,
      ];
    //}


    $form['application_name'] = [
      '#type' => 'textfield',
      '#title' => t('Assign Application Name'),
      '#default_value' => $this->getValues()['application_name'] ?? [],
      '#required' => TRUE,
    ];

    $form['clean_css'] = [
      '#type' => 'checkbox',
      '#title' => t('Clean CSS and JS file internal path'),
      '#default_value' => $this->getValues()['clean_css'] ?? [],
    ];

    $form['clean_approot'] = [
      '#type' => 'checkbox',
      '#title' => t('Clean app root file internal path'),
      '#default_value' => $this->getValues()['clean_approot'] ?? [],
    ];


    $form['file_id_unique'] = [
      '#type' => 'hidden',
      '#value' => $fieldId,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldNames(): array {
    return [
      'application_name',
      'select_html_file',
      'clean_css',
      'clean_approot',
      'file_id_unique'
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldsValidators(): array {
    return [
      'application_name' => [
        new ValidatorRequired("Please assign name of the application"),
      ],
      'select_html_file' => [
        new ValidatorRequired("Please select the app root file"),
      ],
    ];
  }

}
