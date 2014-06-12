<?php
namespace Drupal\layout\Plugin\Layout;

use Drupal\Core\Plugin\PluginBase;

/**
 * Provides a base class for Layout plugins.
 */
abstract class LayoutBase extends PluginBase implements LayoutInterface {
  /**
   * {@inheritdoc}
   */
  function getRegionNames() {
    $regions = $this->getRegionDefinitions();
    foreach ($regions as $region_id => $region_definition) {
      $return[$region_id] = $region_definition['label'];
    }
    return $return;
  }

  /**
   * {@inheritdoc}
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
        $region['region_id'] = $key;
      }
      $regions[$key] = $region;
    }
    return $regions;
  }
}
