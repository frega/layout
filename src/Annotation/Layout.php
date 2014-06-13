<?php

/**
 * @file
 * Contains Drupal\layout\Annotation\Layout.
 */

namespace Drupal\layout\Annotation;
use Drupal\Component\Annotation\Plugin;

/**
  Note - this is the minimum that ds.module requires:

  'label' => t('Four column - equal width'),
  'path' => $path . '/layouts/ds_4col',
  'regions' => array(
    'first' => t('First'),
    'second' => t('Second'),
    'third' => t('Third'),
    'fourth' => t('Fourth'),
  ),
  'css' => TRUE,
  'image' => TRUE,

  Do we need:
    - weight:
    - category:
*/

/**
 * Defines a Layout annotation object.
 *
 * @todo: Document other annotation keys.
 *
 * @Annotation
 */
class Layout extends Plugin {

  /**
   * The layout type.
   *
   *  - full: Layout for the whole page.
   *  - page: Layout for the main page response.
   *  - partial: A partial layout that is typically used for sub-regions.
   *
   * @var string
   */
  public $type = 'page';
}
