<?php

/**
 * @file
 * Contains \Drupal\page_layout\LayoutBlockAndContextProviderInterface.
 */
namespace Drupal\page_layout;


interface LayoutBlockAndContextProviderInterface {

  /**
   * @return array
   */
  public function getContexts();

  /**
   * Returns all block plugin instances in given region.
   *
   * @param $region_id
   * @return \Drupal\block\BlockPluginInterface[]
   */
  public function getBlocksByRegion($region_id);

  /**
   * Returns the plugin collection of LayoutRegions.
   *
   * @return \Drupal\page_layout\Plugin\LayoutRegion\LayoutRegionPluginCollection
   */
  public function getLayoutRegions();

}
