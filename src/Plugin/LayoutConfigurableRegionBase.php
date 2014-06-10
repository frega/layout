<?php
namespace Drupal\layout\Plugin;

use Drupal\Core\Plugin\PluginBase;
use Drupal\layout\Plugin\LayoutRegionInterface;

/**
 * Provides a base class for Layout plugins.
 */
abstract class LayoutConfigurableRegionBase extends PluginBase implements LayoutConfigurableRegionInterface {
  /**
   * {@inheritdoc}
   */
  public function label() {
    return $this->configuration['label'];
  }

  /**
   * {@inheritdoc}
   */
  public function adminLabel() {
    return $this->configuration['admin_label'];
  }

  /**
   * {@inheritdoc}
   */
  public function id() {
    return $this->configuration['uuid'];
  }

  /**
   * {@inheritdoc}
   */
  public function getWeight() {
    return (int) $this->configuration['weight'];
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
