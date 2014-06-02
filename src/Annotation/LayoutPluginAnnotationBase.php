<?php

/**
 * @file
 * Contains \Drupal\layout\Plugin\Annotation\LayoutPluginAnnotationBase.
 */

namespace Drupal\layout\Annotation;

use Drupal\Component\Annotation\AnnotationInterface;
use Drupal\Component\Annotation\Plugin;

/**
 * Defines an abstract base class for all layout plugin annotations.
 */
abstract class LayoutPluginAnnotationBase extends Plugin implements AnnotationInterface {
  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The plugin title used in the layout UI.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $title = '';

  /**
   * (optional) The short title used in the layout UI.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $short_title = '';

  /**
   * The administrative name.
   *
   * The name is displayed on the layout overview and also used as default name
   * for new displays.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $admin = '';

  /**
   * A short help string; this is displayed in the layout UI.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $help = '';

  /**
   * A class to make the plugin derivative aware.
   *
   * @var string
   *
   * @see \Drupal\Component\Plugin\Discovery\DerivativeDiscoveryDecorator
   */
  public $derivative;

  /**
   * Whether or not to register a theme function automatically.
   *
   * @var bool (optional)
   */
  public $register_theme = TRUE;

  /**
   * A list of places where contextual links should be added.
   * For example:
   * @code
   * array(
   *   'page',
   *   'block',
   * )
   * @endcode
   *
   * If you don't specify it there will be contextual links rendered for all
   * displays of a view. If this is not set or regions have been specified,
   * layout will display an option to 'hide contextual links'. Use an empty
   * array to disable.
   *
   * @var array
   */
  public $contextual_links_locations;

  /**
   * The theme function used to render the display's output.
   *
   * @return string
   */
  public $theme;

  /**
   * Whether the plugin should be not selectable in the UI.
   *
   * If it's set to TRUE, you can still use it via the API in config files.
   *
   * @var bool
   */
  public $no_ui;

}
