<?php

/**
 * @file
 * Contains \Drupal\page_layout\Plugin\LayoutConfigurableRegionInterface.
 */

namespace Drupal\page_layout\Plugin\LayoutRegion;

use Drupal\page_layout\Plugin\LayoutRegion\LayoutRegionInterface;
use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Core\Plugin\PluginFormInterface;

/**
 * Provides an interface for configurable Layout Region plugins.
 */
interface LayoutConfigurableRegionInterface extends LayoutRegionInterface, ConfigurablePluginInterface, PluginFormInterface {
  /**
   * Sets the weight of the layout region.
   *
   * @param int $weight
   *   The weight to set.
   */
  public function setWeight($weight);

  // @todo: add missing methods here.
}
