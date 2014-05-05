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
 * Prepares variables for layout templates.
 *
 * Default template: layout-layout.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - layout: The Layout object.
 */
function template_preprocess_layout_layout(&$variables) {
  var_dump('Hello layout_layout', $variables);
}

/**
 * Prepares variables for layout templates.
 *
 * Default template: layout-layout.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - components: an array of LayoutComponent
 */
function template_preprocess_layout_container(&$variables) {
}

/**
 * Prepares variables for layout templates.
 *
 * Default template: layout-layout.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - component: an instance of LayoutComponent
 */
function template_preprocess_layout_component(&$variables) {
}
