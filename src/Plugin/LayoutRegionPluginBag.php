<?php

/**
 * @file
 * Contains \Drupal\layout\Plugin\LayoutRegionPluginBag.
 */

namespace Drupal\layout\Plugin;

use Drupal\Core\Plugin\DefaultPluginBag;

/**
 * Provides a collection of block plugins.
 */
class LayoutRegionPluginBag extends DefaultPluginBag {

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
