<?php

namespace Drupal\layout;


use Drupal\block\BlockPluginInterface;

class Layouts {
  /**
   * Returns the plugin manager for a certain layout plugin type.
   *
   * @param string $type
   *   The plugin type, for example filter.
   *
   * @return \Drupal\layout\Plugin\LayoutPluginManager
   */
  public static function pluginManager($type) {
    return \Drupal::service('plugin.manager.layout.' . $type);
  }

  /**
   * Returns the layout executable factory service.
   *
   * @return \Drupal\layout\LayoutExecutableFactory
   *   Returns layout executable factory.
   */
  public static function executableFactory() {
    return \Drupal::service('layout.executable');
  }

  /**
   * Converts a BlockPluginInterface to a minimal array (id, label, weight and region/container)
   *
   * @param BlockPluginInterface $block
   * @return array
   */
  public static function blockToArray(BlockPluginInterface $block) {
    $config = $block->getConfiguration();
    $settings = isset($config['settings']) ? $config['settings'] : array();
    $definition = $block->getPluginDefinition();
    if (isset($config['label']) && !empty($config['label'])) {
      $label = $config['label'];
    }
    else {
      $label = isset($definition['admin_label']) ? $definition['admin_label'] : $block->getPluginId();
    }
    return array(
      'id' => $config['uuid'],
      'label' => $label,
      'weight' => isset($config['weight']) ? $config['weight'] : 0,
      'container' => $config['region']
    );
  }
}
