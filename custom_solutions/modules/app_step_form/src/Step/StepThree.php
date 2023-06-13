<?php

namespace Drupal\app_step_form\Step;

use Drupal\app_step_form\Button\StepThreeFinishButton;
use Drupal\app_step_form\Button\StepThreePreviousButton;
use Drupal\app_step_form\Validator\ValidatorRequired;

/**
 * Class StepThree.
 *
 * @package Drupal\app_step_form\Step
 */
class StepThree extends BaseStep {

  /**
   * {@inheritdoc}
   */
  protected function setStep(): int {
    return StepsEnum::STEP_THREE;
  }

  /**
   * {@inheritdoc}
   */
  public function getButtons(): array {
    return [
      new StepThreePreviousButton(),
      new StepThreeFinishButton(),
    ];
  }


  /**
   * {@inheritdoc}
   */
  public function buildStepFormElements(): array {

    $store = \Drupal::service('tempstore.private')->get('app_step_form');
    $stepOneFormState = $store->get('form_state_' . StepsEnum::STEP_ONE);
    $stepTwoFormState = $store->get('form_state_' . StepsEnum::STEP_TWO);

    $previewFiles = $store->get('preview_files');
    $libraryFiles = $store->get('library_names');
    $folderName = $store->get('folder_name');
    $repository = $stepOneFormState['public_or_private'];
    if($repository == 'private') {
      $module_handler = \Drupal::service('module_handler');
      $filesDirectory = $module_handler->getModule('app_step_form')->getPath() . '/';
      $sitePublicFilesPath = $module_handler->getModule('app_step_form')->getPath();
    } else {
      $filesDirectory = 'public://';
      $sitePublicFilesPath = '/sites/default/files';
    }



    //@TODO replace with dynamic method
    $cleanCSSandJS = $stepTwoFormState['clean_css'];
    $cleanApproot = $stepTwoFormState['clean_approot'];
    $selectedFileId = $stepTwoFormState['select_html_file'];
    $indexFileBasePath = $previewFiles[$selectedFileId];

    $fileId = $stepOneFormState['angular_folder_zip'][0];
    if (!empty($fileId)) {
      $folderPathURI = $filesDirectory .'angular_component/angular_component_' . $fileId;
      $relativeLibraryUrl = $sitePublicFilesPath . '/angular_component/angular_component_' . $fileId . '/' . $folderName . '/';
      $appRootFile = $folderPathURI . '/' . $indexFileBasePath;


      if($cleanApproot == 1) {
        $updatedHTML = $this->_cleanAppRootFile($appRootFile, $libraryFiles, $relativeLibraryUrl);
      }
      if($cleanCSSandJS == 1) {
        $this->_cleanCssJsFiles($folderPathURI . '/' . $folderName . '/' , $libraryFiles, $relativeLibraryUrl);
      }
    }

    $form['body'] = [
      '#type' => 'textarea',
      '#title' => t('Body'),
      '#value' => $updatedHTML,
      '#default_value' => $this->getValues()['body'] ?? [],
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldNames(): array {
    return [
      'body',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldsValidators(): array {
    return [
      'body' => [
        new ValidatorRequired("Tell me where I can find your LinkedIn please."),
      ],
    ];
  }


  /**
   * @param string $appRootFile
   * this param will rerun the index file full path
   * @param array $libraryFiles
   * this param will contain file names to check and update their path
   *
   * @return void
   */
  public function _cleanAppRootFile(string $appRootFile = '', array $libraryFiles = [], string $updatedPath = ''): string{
    $updatedRawHtml = '';
    if (empty($appRootFile) && empty($libraryFiles) && empty($updatedPath)) {
      return $updatedRawHtml;
    }
    if (file_exists($appRootFile)) {
      $htmlRaw = file_get_contents($appRootFile);
      foreach ($libraryFiles as $singleFile) {
        if (empty($updatedRawHtml)) {
          $updatedRawHtml = str_replace('"' . $singleFile . '"', '"' . $updatedPath . $singleFile . '"', $htmlRaw);
        }
        else {
          $updatedRawHtml = str_replace('"' . $singleFile . '"', '"' . $updatedPath . $singleFile . '"', $updatedRawHtml);
        }
      }

      $updatedRawHtml = str_replace("<head>", "<div>", $updatedRawHtml);
      $updatedRawHtml = str_replace("<head/>", "<div/>", $updatedRawHtml);
      $updatedRawHtml = str_replace("<body>", "<div>", $updatedRawHtml);
      $updatedRawHtml = str_replace("<body/>", "<div>", $updatedRawHtml);
      file_put_contents($appRootFile, $updatedRawHtml);
      return $updatedRawHtml;
    }
  }


  /**
   * @param string $relativeLibraryUrl
   * @param array $libraryFiles
   * this param will contain file names to check and update their path
   *
   * @return void
   */
  public function _cleanCssJsFiles(string $relativeLibraryUrl = '', array $libraryFiles = [], string $updatedpath = ''): void {
    if (empty($relativeLibraryUrl) && empty($libraryFiles)) {
      return;
    }
    foreach ($libraryFiles as $singleFile) {
      $rawData = file_get_contents($relativeLibraryUrl . $singleFile);
      $updatedRawHtml = str_replace('/assets/',   $updatedpath .  'assets/' , $rawData);
      file_put_contents($relativeLibraryUrl . $singleFile, $updatedRawHtml);
    }
  }
}
