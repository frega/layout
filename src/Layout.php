<?php

namespace Drupal\layout;


use Drupal\block\BlockPluginInterface;
use Drupal\page_manager\PageInterface;
use Drupal\page_manager\Plugin\PageVariantInterface;

class Layout {
  /**
   * Returns the plugin manager for the Layout plugin type.
   *
   * @param string $type
   *   The plugin type, for example filter.
   *
   * @return \Drupal\layout\Plugin\LayoutPluginManager
   */
  public static function layoutPluginManager() {
    return \Drupal::service('plugin.manager.layout');
  }

  /**
   * Returns the plugin manager for the LayoutRegion plugin type.
   *
   * @param string $type
   *   The plugin type, for example filter.
   *
   * @return \Drupal\layout\Plugin\LayoutRegionPluginManager
   */
  public static function layoutRegionPluginManager() {
    return \Drupal::service('plugin.manager.layout.region');
  }

  /**
   * Return all available layout as an options array.
   *
   * If group_by_category option/parameter passed group the options by
   * category.
   *
   * @return array
   */
  public static function getLayoutOptions($params = array()) {
    $layoutManager = \Drupal::service('plugin.manager.layout');
    // Sort the plugins first by category, then by label.
    $plugins = $layoutManager->getDefinitions();
    $options = array();
    $group_by_category = !empty($params['group_by_category']);
    foreach ($plugins as $id => $plugin) {
      if ($group_by_category) {
        $category = isset($plugin['category']) ? $plugin['category'] : 'default';
        if (!isset($options[$category])) {
          $options[$category] = array();
        }
        $options[$category][$id] = $plugin['label'];
      }
      else {
        $options[$id] = $plugin['label'];
      }
    }
    return $options;
  }
}
