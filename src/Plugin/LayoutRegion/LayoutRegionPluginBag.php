<?php

/**
 * @file
 * Contains \Drupal\page_layout\Plugin\LayoutRegionPluginBag.
 */

namespace Drupal\layout\Plugin\LayoutRegion;

use Drupal\Core\Plugin\DefaultPluginBag;

/**
 * Provides a collection of layout region plugins.
 */
class LayoutRegionPluginBag extends DefaultPluginBag {
  // @todo: implement sortHelper for configurable and non-configurable LayoutRegion plugins.
  /**
   * {@inheritdoc}
   */
  public function sortHelper($aID, $bID) {
    $a_weight = $this->get($aID)->getWeight();
    $b_weight = $this->get($bID)->getWeight();
    if ($a_weight == $b_weight) {
      return 0;
    }

    return ($a_weight < $b_weight) ? -1 : 1;
  }

}
