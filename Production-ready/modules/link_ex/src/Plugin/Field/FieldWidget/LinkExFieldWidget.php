<?php

namespace Drupal\link_ex\Plugin\Field\FieldWidget;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\imce\Imce;
use Drupal\link\Plugin\Field\FieldWidget\LinkWidget;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'link_ex' widget.
 *
 * @FieldWidget(
 *   id = "link_ex",
 *   label = @Translation("Link (Ex)"),
 *   field_types = {
 *     "link"
 *   }
 * )
 */
class LinkExFieldWidget extends LinkWidget implements ContainerFactoryPluginInterface {

  /**
   * THe module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;
  /**
   * Constructs a LinkExFieldWidget object.
   *
   * @param string $plugin_id
   *   The plugin_id for the widget.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the widget is associated.
   * @param array $settings
   *   The widget settings.
   * @param array $third_party_settings
   *   Any third party settings.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, array $third_party_settings, ModuleHandlerInterface $module_handler) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings);
    $this->moduleHandler = $module_handler;

  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['third_party_settings'],
      $container->get('module_handler')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {

    // @todo default for attributes ?

    return [
      'placeholder_url' => '',
      'placeholder_title' => '',
      'enabled_attributes' => [],
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);
    // Add each of the enabled attributes.
    // @todo move this to plugins that nominate form and label.

    $item = $items[$delta];

    $options = $item->get('options')->getValue();
    $attributes = isset($options['attributes']) ? $options['attributes'] : [];
    $element['options']['attributes'] = [
      '#type' => 'details',
      '#title' => $this->t('Attributes'),
      '#tree' => TRUE,
    // count($attributes),
      '#open' => FALSE,
    ];

    $attOptions = $this->attributeOptions();

    // Remove hidden options as.
    $plugin_definitions = array_diff_key($attOptions, array_filter($attOptions, function ($var) {
        return ($var['#type'] === 'hidden');
    }));

    $enabled_attributes = array_keys(array_filter($this->getSetting('enabled_attributes')));

    foreach ($enabled_attributes as $attribute) {
      if (isset($plugin_definitions[$attribute])) {
        $element['options']['attributes'][$attribute] = $plugin_definitions[$attribute];
        $element['options']['attributes'][$attribute]['#default_value'] = (isset($attributes[$attribute]) ? $attributes[$attribute] : '');
      }
    }

    // @todo setup based on the attribute configuration
    if (in_array('imce', $enabled_attributes)) {
      $element['uri']['#attributes']['data-link_ex-file_browser'] = 'imce';
    }
    if (in_array('imce_private', $enabled_attributes)) {
      $element['uri']['#attributes']['data-link_ex-file_private'] = 'imce_private';
    }

    if ($this->moduleHandler->moduleExists('imce') && Imce::access()) {
      $element['#attached']['library'][] = 'link_ex/drupal.link_ex.imce';
    }

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::settingsForm($form, $form_state);
    $selected = array_keys(array_filter($this->getSetting('enabled_attributes')));
    $attOptions = $this->attributeOptions();
    $options = array_combine(array_keys($attOptions), array_column($attOptions, '#title'));

    $element['enabled_attributes'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Enabled attributes'),
      '#options' => $options,
      '#default_value' => array_combine($selected, $selected),
      '#description' => $this->t('Select the attributes to allow the user to edit.'),
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    return array_map(function (array $value) {
      $value['options']['attributes'] = array_filter($value['options']['attributes'], function ($attribute) {
        return $attribute !== "";
      });
      return $value;
    }, $values);
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();
    $enabled_attributes = array_filter($this->getSetting('enabled_attributes'));
    if ($enabled_attributes) {
      $summary[] = $this->t('With attributes: @attributes', ['@attributes' => implode(', ', array_keys($enabled_attributes))]);
    }
    return $summary;
  }

  /**
   * Setting form attribute options for link.
   */
  public function attributeOptions() {

    $option['id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Link ID'),
      '#placeholder' => $this->t('ID attribute'),
      '#default_value' => NULL,
      '#maxlength' => 255,
      '#description' => $this->t('The id attribute.'),
    ];

    $option['rel'] = [
      '#type' => 'select',
      '#title' => $this->t('Rel'),
      '#default_value' => NULL,
      '#options' => [
        'alternate' => 'alternate',
        'author' => 'author',
        'bookmark' => 'bookmark',
        'external' => 'external',
        'help' => 'help',
        'license' => 'license',
        'next' => 'next',
        'nofollow' => 'nofollow',
        'noreferrer' => 'noreferrer',
        'noopener' => 'noopener',
        'prev' => 'prev',
        'search' => 'search',
        'tag' => 'tag',
      ],
      '#required' => FALSE,
      '#empty_value' => '',
      '#description' => $this->t('Relationship between the current document and the linked document'),

    ];

    $option['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Name'),
      '#placeholder' => $this->t('Name attribute'),
      '#default_value' => NULL,
      '#maxlength' => 255,
      '#description' => $this->t('Not supported in HTML5'),
    ];

    $option['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#placeholder' => $this->t('Title attribute'),
      '#default_value' => NULL,
      '#maxlength' => 128,
      '#description' => $this->t('The title attribute. Use %filename, %size, %extension or %url variable to specify in title.', ['%filename' => '<filename>', '%size' => '<size>', '%extension' => '<extension>', '%url' => '<url>']),
    ];

    $option['target'] = [
      '#type' => 'select',
      '#title' => $this->t('Link target'),
      '#options' => [
        '_self'  => $this->t('Same window (_self)'),
        '_blank' => $this->t('New window (_blank)'),
      ],
      '#required' => FALSE,
      '#empty_value' => '',
      '#description' => $this->t('Specifies where to open the linked document'),
    ];

    $option['class'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Class'),
      '#placeholder' => $this->t('CSS classs'),
      '#default_value' => NULL,
      '#maxlength' => 255,
      '#description' => $this->t('CSS class(s) for the element. Use %mime, %extension variable to specify in class.', ['%mime' => '<mime>', '%extension' => '<extension>']),
    ];

    $option['accesskey'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Accesskey'),
      '#placeholder' => $this->t('Link accesskey'),
      '#default_value' => NULL,
      '#maxlength' => 255,
      '#description' => '',
    ];
    $option['download'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Download'),
      '#placeholder' => $this->t('Download filename'),
      '#default_value' => NULL,
      '#maxlength' => 255,
      '#description' => $this->t('Html5 download attribute specifies that the target will be downloaded on link click. Use %filename for file name or %blank to leave attribute blank.', ['%filename' => '<filename>', '%blank' => '<blank>']),
    ];

    $option['imce'] = [
      '#type' => 'hidden',
      '#title' => $this->t('Imce File Manager'),
    ];
    $option['imce_private'] = [
      '#type' => 'hidden',
      '#title' => $this->t('Imce File Manager (Private)'),
    ];
    return $option;
  }

}
