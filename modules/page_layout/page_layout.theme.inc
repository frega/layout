<?php

/**
 * @file
 * Preprocessors and helper functions to make theming easier.
 */

use Drupal\Component\Utility\String;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Language\Language;
use Drupal\Core\Template\Attribute;

/**
 * Prepares variables for Layouts.
 *
 * Default template: layout-layout.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - layout: The Layout object.
 */
function template_preprocess_layout_layout(&$variables) {
}

/**
 * Prepares variables for layout regions.
 *
 * Default template: layout-region.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - blocks: an array of LayoutComponent
 */
function template_preprocess_layout_region(&$variables) {
}

/**
 * Prepares variables for a layout block.
 *
 * Default template: layout-layout.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - block: an instance of LayoutComponent
 */
function template_preprocess_layout_block(&$variables) {
}
