<?php

/**
 * @file
 * Contains \Drupal\layout\Plugin\LayoutContainerBase.
 */

namespace Drupal\layout\Plugin;

use Drupal\Core\Plugin\PluginBase;
use Drupal\Core\Plugin\PluginDependencyTrait;

use Drupal\layout\Plugin\LayoutContainerInterface;

/**
 * Provides a base class for Layout plugins.
 */
abstract class LayoutPluginBase extends PluginBase implements LayoutContainerInterface {
  /**
   * {@inheritdoc}
   */
  public function label() {
    return isset($this->configuration['label']) ? $this->configuration['label'] : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function id() {
    return isset($this->configuration['uuid']) ? $this->configuration['uuid'] : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getWeight() {
    return isset($this->configuration['weight']) ? $this->configuration['weight'] : 0;
  }

  /**
   * {@inheritdoc}
   */
  public function setWeight($weight) {
    $this->configuration['weight'] = (int) $weight;
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    return array(
      'id' => $this->getPluginId(),
    ) + $this->configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration) {
    $this->configuration = $configuration + $this->defaultConfiguration();
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return array(
      'label' => '',
      'uuid' => '',
      'weight' => 0
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->setConfiguration($configuration);
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, array &$form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, array &$form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, array &$form_state) {
  }

  /**
   * Returns the UUID generator.
   *
   * @return \Drupal\Component\Uuid\UuidInterface
   */
  protected function uuidGenerator() {
    return \Drupal::service('uuid');
  }

}
