<?php

/**
 * @file
 * Contains \Drupal\layout\Plugin\Layout\LayoutBlockAndContextProviderInterface.
 */
namespace Drupal\layout\Plugin\Layout;


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
   * @return \Drupal\layout\Plugin\LayoutRegion\LayoutRegionPluginBag
   */
  public function getLayoutRegions();

} 
