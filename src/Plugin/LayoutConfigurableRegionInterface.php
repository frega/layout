<?php

/**
 * @file
 * Contains \Drupal\layout\Plugin\LayoutConfigurableRegionInterface.
 */

namespace Drupal\layout\Plugin;

use Drupal\layout\Plugin\LayoutRegionInterface;
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
