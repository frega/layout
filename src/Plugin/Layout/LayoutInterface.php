<?php

/**
 * @file
 * Contains \Drupal\layout\Plugin\LayoutPluginInterface.
 */

namespace Drupal\layout\Plugin\Layout;
use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Provides an interface for static Layout plugins.
 */
interface LayoutInterface extends PluginInspectionInterface {
  /**
   * Returns the human-readable list of regions keyed by machine name.
   *
   * @return array
   *   An array of human-readable region names keyed by machine name.
   */
  public function getRegionNames();

  /**
   * Returns the information on regions keyed by machine name.
   *
   * @return array
   *   An array of information on regions keyed by machine name.
   */
  public function getRegionDefinitions();
}
