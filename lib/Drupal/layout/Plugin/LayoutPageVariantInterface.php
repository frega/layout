<?php
namespace Drupal\layout\Plugin;

use \Drupal\page_manager\Plugin\PageVariantInterface;

interface LayoutPageVariantInterface extends PageVariantInterface {
  public function addLayoutRegion(array $configuration);
  public function getLayoutRegion($layout_region_id);
  /**
   * Removes a layout region ids
   */
  public function removeLayoutRegion($layout_region_id);
  public function getLayoutRegions();

  public function getLayoutTemplateId();
  public function getLayoutTemplate($reset = FALSE);


  public function getBlocksByRegion($region_id);

}
