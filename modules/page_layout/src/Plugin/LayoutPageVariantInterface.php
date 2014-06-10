<?php
namespace Drupal\page_layout\Plugin;

use \Drupal\page_manager\Plugin\PageVariantInterface;

interface LayoutPageVariantInterface extends PageVariantInterface {
  /**
   * Adds a LayoutRegion to the layout regions bag.
   *
   * @param array $configuration
   * @return mixed
   */
  public function addLayoutRegion(array $configuration);

  /**
   * Retrieves a LayoutRegion instance from the layout regions bag.
   *
   * @param $layout_region_id
   *
   * @return \Drupal\page_layout\Plugin\LayoutRegionPluginInterface
   */
  public function getLayoutRegion($layout_region_id);

  /**
   * Remove a LayoutRegion instance from the layout regions bag.
   */
  public function removeLayoutRegion($layout_region_id);

  /**
   * Returns the plugin bag of LayoutRegions.
   *
   * @return \Drupal\page_layout\Plugin\LayoutRegionPluginBag
   */
  public function getLayoutRegions();

  /**
   * Returns the id the template.
   *
   * @return mixed
   */
  public function getLayoutId();

  /**
   * Returns the
   *
   * @param bool $reset
   * @return mixed
   */
  public function getLayout($reset = FALSE);

  public function getBlocksByRegion($region_id);
}
