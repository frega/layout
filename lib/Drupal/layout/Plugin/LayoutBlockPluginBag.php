<?php

/**
 * @file
 * Contains \Drupal\page_manager\Plugin\BlockPluginBag.
 */

namespace Drupal\layout\Plugin;

use Drupal\block\BlockPluginInterface;
use Drupal\Core\Plugin\DefaultPluginBag;

/**
 * Provides a collection of block plugins.
 */
class LayoutBlockPluginBag extends DefaultPluginBag {

  /**
   * {@inheritdoc}
   *
   * @return \Drupal\block\BlockPluginInterface
   */
  public function &get($instance_id) {
    return parent::get($instance_id);
  }

  /**
   * Returns all blocks keyed by their region.
   *
   * @return array
   *   An associative array keyed by region, containing an associative array of
   *   block plugins.
   */
  public function getAllByRegion() {
    $region_assignments = array();
    foreach ($this as $block_id => $block) {
      $configuration = $block->getConfiguration();
      $region = isset($configuration['region']) ? $configuration['region'] : NULL;
      $region_assignments[$region][$block_id] = $block;
    }
    foreach ($region_assignments as $region => $region_assignment) {
      // @todo Determine the reason this needs error suppression.
      @uasort($region_assignment, function (BlockPluginInterface $a, BlockPluginInterface $b) {
        $a_config = $a->getConfiguration();
        $a_weight = isset($a_config['weight']) ? $a_config['weight'] : 0;
        $b_config = $b->getConfiguration();
        $b_weight = isset($b_config['weight']) ? $b_config['weight'] : 0;
        if ($a_weight == $b_weight) {
          return strcmp($a->label(), $b->label());
        }
        return $a_weight > $b_weight ? 1 : -1;
      });
      $region_assignments[$region] = $region_assignment;
    }
    return $region_assignments;
  }

  public function getRegionByBlockId($block_id) {
    $block = $this->get($block_id);
    if (!$block) {
      throw Exception('Invalid block id given');
    }
    $configuration = $block->getConfiguration();
    return isset($configuration['region']) ? $configuration['region'] : NULL;
  }
}
