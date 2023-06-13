<?php

namespace Drupal\app_step_form\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\app_step_form\Manager\StepManager;
use Drupal\app_step_form\Step\StepInterface;
use Drupal\app_step_form\Step\StepsEnum;
use Drupal\block_content\Entity\BlockContent;


/**
 * Provides multi step ajax example form to create the angular app.
 *
 * @package Drupal\app_step_form\Form
 */
class BuildAppForm extends FormBase {
  use StringTranslationTrait;

  /**
   * Step Id.
   */
  protected int $stepId;

  /**
   * Multi steps of the form.
   *
   * @var \Drupal\app_step_form\Step\StepInterface
   */
  protected StepInterface $step;

  /**
   * Step manager instance.
   *
   * @var \Drupal\app_step_form\Manager\StepManager
   */
  protected StepManager $stepManager;


  /**
   * {@inheritdoc}
   */
  public function __construct() {
    $this->stepId = StepsEnum::STEP_ONE;
    $this->stepManager = new StepManager();
  }


  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'app_step_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form['wrapper-messages'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => 'messages-wrapper',
      ],
    ];

    $form['wrapper'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => 'form-wrapper',
      ],
    ];

    // Get step from step manager.
    $this->step = $this->stepManager->getStep($this->stepId);

    // Attach step form elements.
    $form['wrapper'] += $this->step->buildStepFormElements();

    // Attach buttons.
    $form['wrapper']['actions']['#type'] = 'actions';
    $buttons = $this->step->getButtons();
    foreach ($buttons as $button) {
      /** @var \Drupal\app_step_form\Button\ButtonInterface $button */
      $form['wrapper']['actions'][$button->getKey()] = $button->build();

      if ($button->ajaxify()) {
        // Add ajax to button.
        $form['wrapper']['actions'][$button->getKey()]['#ajax'] = [
          'callback' => [$this, 'loadStep'],
          'wrapper' => 'form-wrapper',
          'effect' => 'fade',
        ];
      }

      $callable = [$this, $button->getSubmitHandler()];
      if ($button->getSubmitHandler() && is_callable($callable)) {
        // Attach submit handler to button, so we can execute it later on..
        $form['wrapper']['actions'][$button->getKey()]['#submit_handler'] = $button->getSubmitHandler();
      }
    }

    return $form;

  }

  /**
   * Ajax callback to load new step.
   *
   * @param array $form
   *   Form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state interface.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   Ajax's response.
   */
  public function loadStep(array &$form, FormStateInterface $form_state): AjaxResponse {
    $response = new AjaxResponse();

    //$messages = Messenger::all();
    if (!empty($messages)) {
      // Form did not validate, get messages and render them.
      $messages = [
        '#theme' => 'status_messages',
        '#message_list' => $messages,
        '#status_headings' => [
          'status' => $this->t('Status message'),
          'error' => $this->t('Error message'),
          'warning' => $this->t('Warning message'),
        ],
      ];
      $response->addCommand(new HtmlCommand('#messages-wrapper', $messages));
    }
    else {
      // Remove messages.
      $response->addCommand(new HtmlCommand('#messages-wrapper', ''));
    }

    // Update Form.
    $response->addCommand(new HtmlCommand('#form-wrapper',
      $form['wrapper']));

    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $triggering_element = $form_state->getTriggeringElement();
    // Only validate if validation doesn't have to be skipped.
    // For example on "previous" button.
    if (empty($triggering_element['#skip_validation']) && $fields_validators = $this->step->getFieldsValidators()) {
      // Validate fields.
      foreach ($fields_validators as $field => $validators) {
        // Validate all validators for field.
        $field_value = $form_state->getValue($field);
        foreach ($validators as $validator) {
          if (!$validator->validates($field_value)) {
            $form_state->setErrorByName($field, $validator->getErrorMessage());
          }
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   * @throws \Drupal\Core\TempStore\TempStoreException
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Save filled values to step. So we can use them as default_value later on.
    $values = [];
    foreach ($this->step->getFieldNames() as $name) {
      $values[$name] = $form_state->getValue($name);
    }
    // @TODO find an alternative to gt the value for individual form on seprate steps to avoid using local storage
    $store = \Drupal::service('tempstore.private')->get('app_step_form');
    $store->set('form_state_' . $this->stepId, $values);

    $this->step->setValues($values);
    // Add step to manager.
    $this->stepManager->addStep($this->step);
    // Set step to navigate to.
    $triggering_element = $form_state->getTriggeringElement();
    $this->stepId = $triggering_element['#goto_step'];

    // If an extra submit handler is set, execute it.
    // We already tested if it is callable before.
    if (isset($triggering_element['#submit_handler'])) {
      $this->{$triggering_element['#submit_handler']}($form, $form_state);
    }

    $form_state->setRebuild(TRUE);
  }

  /**
   * Submit handler for last step of form.
   *
   * @param array $form
   *   Form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state interface.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function submitValues(array &$form, FormStateInterface $form_state) {
    $store = \Drupal::service('tempstore.private')->get('app_step_form');
    $firstStepData = $store->get('form_state_1');
    $secondStepData = $store->get('form_state_2');
    $lastStepValues = $form_state->getValues();
    $iframeData = $blockName = '';
    if(!empty($lastStepValues) && !empty($secondStepData)) {
      $iframeData = $lastStepValues['body'];
      $blockName = $secondStepData['application_name'];
      $fileId = $secondStepData['file_id_unique'];
      $permissionType = $firstStepData['public_or_private'];
    }

    $block = BlockContent::create([
      'info' => $blockName,
      'type' => 'app_block',
      'body' => [
        'value' => $iframeData,
        'format' => 'full_html'
      ],
      'field_unique_file_id' => $fileId,
      'field_permission_type' => $permissionType,
      'langcode' => 'en'
    ]);
    $block->save();
  }

}
