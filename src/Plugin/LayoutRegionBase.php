<?php
namespace Drupal\layout\Plugin;

use Drupal\Core\Plugin\PluginBase;
use Drupal\layout\Plugin\LayoutRegionInterface;

/**
 * Provides a base class for Layout plugins.
 */
abstract class LayoutRegionBase extends PluginBase implements LayoutRegionInterface {
  /**
   * {@inheritdoc}
   */
  public function label() {
    return $this->pluginDefinition['label'];
  }

  /**
   * {@inheritdoc}
   */
  public function adminLabel() {
    return $this->pluginDefinition['admin_label'];
  }

  /**
   * {@inheritdoc}
   */
  public function id() {
    return $this->pluginDefinition['id'];
  }

  /**
   * {@inheritdoc}
   */
  public function getWeight() {
    return (int) $this->pluginDefinition['weight'];
  }
}
