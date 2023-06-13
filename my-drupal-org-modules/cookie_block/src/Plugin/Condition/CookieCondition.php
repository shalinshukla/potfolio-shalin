<?php

namespace Drupal\cookie_block\Plugin\Condition;

use Drupal\Core\Condition\ConditionPluginBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides a 'Cookie' condition.
 *
 * @Condition(
 *   id = "cookie",
 *   label = @Translation("Cookie"),
 * )
 */
class CookieCondition extends ConditionPluginBase implements ContainerFactoryPluginInterface {

  use StringTranslationTrait;

  /**
   * The current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * Creates a new Cookie instance.
   *
   * @param array $configuration
   *   The plugin configuration, i.e. an array with configuration values keyed
   *   by configuration option name. The special key 'context' may be used to
   *   initialize the defined contexts by setting it to an array of context
   *   values keyed by context names.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, Request $request) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->request = $request;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('request_stack')->getCurrentRequest()
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {

    $form['cookie_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Cookie ID'),
      '#default_value' => $this->configuration['cookie_id'],
      '#description' => $this->t('The id for the cookie.'),
    ];
    $form['cookie_value'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Cookie value'),
      '#default_value' => $this->configuration['cookie_value'],
      '#description' => $this->t('The value for the cookie.'),
    ];

    return parent::buildConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['cookie_id'] = $form_state->getValue('cookie_id');
    $this->configuration['cookie_value'] = $form_state->getValue('cookie_value');
    parent::submitConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function summary() {
    if (!empty($this->configuration['negate'])) {
      return $this->t('The cookie @cookie_id is not @cookie_value.', [
        '@cookie_id' => $this->configuration['cookie_id'],
        '@cookie_value' => $this->configuration['cookie_value'],
      ]);
    }
    return $this->t('The cookie @cookie_id is @cookie_value.', [
      '@cookie_id' => $this->configuration['cookie_id'],
      '@cookie_value' => $this->configuration['cookie_value'],
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate() {

    $cookies = $this->request->cookies;
    $systemCookie = $cookies->get($this->configuration['cookie_id']);
    $cookie = $this->configuration['cookie_value'];

    if (!empty($systemCookie)) {
      if ($systemCookie == $cookie) {
        return TRUE;
      }
      else {
        return FALSE;
      }
    }
    else {
      return FALSE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $options = parent::defaultConfiguration();
    $options['cookie_id'] = '';
    $options['cookie_value'] = '';
    return $options;
  }

}
