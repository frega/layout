<?php

/**
 * @file
 * Contains \Drupal\layout\Plugin\Layout\LayoutBase.
 */

namespace Drupal\layout\Plugin\Layout;

use Drupal\Core\Plugin\PluginBase;
use Drupal\layout\Layout;
use Drupal\layout\Plugin\LayoutRegion\LayoutRegionPluginBag;

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
    return Layout::getNormalizedLayoutRegionDefinitions($this->pluginDefinition['regions']);
  }
}
