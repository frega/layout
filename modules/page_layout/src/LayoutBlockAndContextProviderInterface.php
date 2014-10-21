<?php

/**
 * @file
 * Contains \Drupal\page_layout\Plugin\Layout\LayoutBlockAndContextProviderInterface.
 */
namespace Drupal\page_layout\Plugin\Layout;


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
   * Returns the plugin bag of LayoutRegions.
   *
   * @return \Drupal\page_layout\Plugin\LayoutRegion\LayoutRegionPluginBag
   */
  public function getLayoutRegions();

} 
