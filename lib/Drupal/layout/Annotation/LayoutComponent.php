<?php

/**
 * @file
 * Contains Drupal\kayout\Annotation\LayoutComponent.
 */

namespace Drupal\layout\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a LayoutComponent annotation object.
 *
 * @Annotation
 */
class LayoutComponent extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The human-readable name of the Layout plugin.
   *
   * @ingroup plugin_translatable
   *
   * @var \Drupal\Core\Annotation\Translation
   */
  public $title;

}
