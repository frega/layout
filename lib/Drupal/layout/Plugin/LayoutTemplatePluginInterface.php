<?php

/**
 * @file
 * Contains \Drupal\layout\Plugin\LayountContainerInterface.
 */

namespace Drupal\layout\Plugin;

use Drupal\layout\Plugin\LayoutPluginInterface;

/**
 * Provides an interface for LayoutTemplate plugins.
 */
interface LayoutTemplatePluginInterface extends LayoutPluginInterface {
  /**
   * Returns array of configuration/definitions for to ensure for this LayoutTemplate
   *
   * @return array
   *  Layout Container Plugin definitions to ensure for this LayoutTemplate
   */
   public function getLayoutContainerPluginDefinitions();

}
