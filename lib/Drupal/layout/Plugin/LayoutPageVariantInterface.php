<?php
namespace Drupal\layout\Plugin;

use \Drupal\page_manager\Plugin\PageVariantInterface;

interface LayoutPageVariantInterface extends PageVariantInterface {
  public function addLayoutContainer(array $configuration);
  public function getLayoutContainer($layout_container_id);
  /**
   * Removes a layout container ids
   */
  public function removeLayoutContainer($layout_container_id);
  public function getLayoutContainers();

  public function getLayoutTemplateId();
  public function getLayoutTemplate($reset = FALSE);


  public function getBlocksByRegion($region_id);

}
