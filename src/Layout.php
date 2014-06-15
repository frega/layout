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
   * @return \Drupal\layout\Plugin\Layout\LayoutPluginManager
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
   * @return \Drupal\layout\Plugin\LayoutRegion\LayoutRegionPluginManager
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

  /**
   * Normalize layout regions into a standardized and normalized form.
   *
   *
   * @param $region_definition
   * @param null $parent_region_id
   * @return array
   */
  public static function getNormalizedLayoutRegionDefinitions($region_definition, $parent_region_id = NULL) {
    if (!isset($region_definition) || !is_array($region_definition)) {
      return array();
    }

    $regions = array();
    foreach ($region_definition as $region_id => $region) {
      if (is_numeric($region_id) && isset($region['region_id'])) {
        $region_id = $region['region_id'];
      }
      else {
        $region['region_id'] = $region_id;
      }

      if (isset($parent_region_id)) {
        $region['parent'] = $parent_region_id;
      }

      if (!isset($region['plugin_id'])) {
        $region['plugin_id'] = 'default';
      }

      if (!isset($region['label'])) {
        $region['label'] = $region['id'];
      }
      else if (is_object($region['label'])) {
        $region['label'] = (string) $region['label'];
      }

      if (isset($region['subregions'])) {
        $subregions = $region['subregions'];
        unset($region['subregions']);
        $regions[$region_id] = $region;
        $normalized_subregions = self::getNormalizedLayoutRegionDefinitions($subregions, $region_id);
        $regions += $normalized_subregions;
      }
      else {
        $regions[$region_id] = $region;
      }
    }

    return $regions;
  }
}
