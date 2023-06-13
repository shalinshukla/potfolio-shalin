<?php

namespace Drupal\app_step_form\Step;

use Drupal\app_step_form\Button\StepOneNextButton;
use Drupal\app_step_form\Validator\ValidatorRequired;

/**
 * Class StepOne.
 *
 * @package Drupal\app_step_form\Step
 */
class StepOne extends BaseStep {

  /**
   * {@inheritdoc}
   */
  protected function setStep() {
    return StepsEnum::STEP_ONE;
  }

  /**
   * {@inheritdoc}
   */
  public function getButtons(): array {
    return [
      new StepOneNextButton(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildStepFormElements(): array {

    $module_handler = \Drupal::service('module_handler');
    $module_path = $module_handler->getModule('app_step_form')->getPath();

    $validators = [
      'file_validate_extensions' => ['zip'],
    ];
    $form['angular_folder_zip'] = [
      '#type' => 'managed_file',
      '#name' => 'angular_folder_zip',
      '#title' => t('Upload Angular Folder ZIP'),
      '#size' => 20,
      '#description' => t('Please upload zip files only'),
      '#upload_validators' => $validators,
      '#upload_location' => 'public://angular_component/',
      '#default_value' => $this->getValues()['angular_folder_zip'] ?? NULL,
      '#description' => t("This upload supports the single app-root file, css and js files must present on the root level of the folder.
                                  If there are local images and json file for the data please add them on the assets folder.
                                  For the example zip please see the attached file.
                                  <a href='/" . $module_path . "/src/example-zip/mkmforms-manager_data_file.zip' download>Example Zip</a>")
    ];

    $form['public_or_private'] = [
      '#type' => 'radios',
      '#title' => t('Make app public or private'),
      '#options' => [
        'public' => t('Public'),
        'private' => t('Private')
      ],
      '#default_value' => $this->getValues()['public_or_private'] ?? [],
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldNames(): array{
    return [
      'angular_folder_zip',
      'public_or_private'
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldsValidators(): array{
    return [
      'angular_folder_zip' => [
        new ValidatorRequired("Please upload the file before going to the next step."),
      ],
    ];
  }

}
