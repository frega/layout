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
    - type: partial, content, full
*/

/**
 * Defines a Layout annotation object.
 *
 * @Annotation
 */
class Layout extends Plugin {
}
