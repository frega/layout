<?php

namespace Drupal\layout_example\Plugin\LayoutRegion;

use Drupal\layout\Plugin\LayoutRegion\LayoutRegionBase;

/**
 * The plugin that handles the example layout region.
 *
 * @LayoutRegion(
 *   id = "layout_region_example",
 *   label = @Translation("Example Layout Region"),
 *   help = @Translation("Layout Region"),
 *   theme = "layout_region_example"
 * )
 */
class LayoutExampleRegion extends LayoutRegionBase {
}
