<?php
namespace Drupal\layout\Plugin;

use Drupal\Core\Plugin\PluginBase;

/**
 * Provides a base class for static Layout plugins.
 */
abstract class LayoutBase extends PluginBase implements LayoutInterface {
  /**
   * Returns the human-readable list of regions keyed by machine name.
   *
   * @return array
   *   An array of human-readable region names keyed by machine name.
   */
  function getRegionNames() {
    $regions = $this->getRegionDefinitions();
    foreach ($regions as $region_id => $region_definition) {
      $return[$region_id] = $region_definition['label'];
    }
    return $return;
  }

  /**
   * Returns the information on regions keyed by machine name.
   *
   * @return array
   *   An array of information on regions keyed by machine name.
   */
  function getRegionDefinitions() {
    if (!isset($this->pluginDefinition['regions']) || !is_array($this->pluginDefinition['regions'])) {
      return array();
    }

    $regions = array();
    foreach ($this->pluginDefinition['regions'] as $key => $region) {
      if (is_numeric($key) && isset($region['id'])) {
        $key = $region['id'];
      }
      else {
        $region['id'] = $key;
      }
      $regions[$key] = $region;
    }
    return $regions;
  }
}
