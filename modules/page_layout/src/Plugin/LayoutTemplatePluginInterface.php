<?php

/**
 * @file
 * Contains \Drupal\page_layout\Plugin\LayoutTemplatePluginInterface.
 */

namespace Drupal\page_layout\Plugin;

use Drupal\page_layout\Plugin\LayoutPluginInterface;

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
