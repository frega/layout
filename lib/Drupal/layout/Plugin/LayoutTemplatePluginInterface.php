<?php

/**
 * @file
 * Contains \Drupal\layout\Plugin\LayoutTemplatePluginInterface.
 */

namespace Drupal\layout\Plugin;

use Drupal\layout\Plugin\LayoutPluginInterface;

/**
 * Provides an interface for LayoutTemplate plugins.
 */
interface LayoutTemplatePluginInterface extends LayoutPluginInterface {
  /**
   * Returns array of configuration/definitions for LayoutRegion instances
   * for this LayoutTemplate.
   *
   * @return array
   *  LayoutRegion Plugin definitions to use for this LayoutTemplate.
   */
   public function getLayoutRegionPluginDefinitions();

}
