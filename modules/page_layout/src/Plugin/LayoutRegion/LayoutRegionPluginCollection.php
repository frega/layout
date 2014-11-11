<?php

/**
 * @file
 * Contains \Drupal\page_layout\Plugin\LayoutRegionPluginCollection.
 */

namespace Drupal\page_layout\Plugin\LayoutRegion;

use Drupal\Core\Plugin\DefaultLazyPluginCollection;

/**
 * Provides a collection of layout region plugins.
 */
class LayoutRegionPluginCollection extends DefaultLazyPluginCollection {
  /**
   * @return \Drupal\page_layout\Plugin\LayoutRegion\LayoutRegionInterface
   */
  public function current() {
    return parent::current();
  }


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
