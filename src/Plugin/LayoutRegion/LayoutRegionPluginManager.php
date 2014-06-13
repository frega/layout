<?php

/**
 * @file
 * Contains \Drupal\layout\Plugin\Layout\LayoutPluginManager.
 */

namespace Drupal\layout\Plugin\LayoutRegion;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Language\LanguageManager;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Plugin type manager for all layouts.
 */
class LayoutRegionPluginManager extends DefaultPluginManager {

  /**
   * Constructs a LayoutPluginManager object.
   *
   * @param string $type
   *   The plugin type, for example filter.
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations,
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    $plugin_definition_annotation_name = 'Drupal\layout\Annotation\LayoutRegion';
    parent::__construct("Plugin/LayoutRegion", $namespaces, $module_handler, $plugin_definition_annotation_name);

    $this->defaults += array(
      'plugin_type' => 'LayoutRegion',
      'register_theme' => TRUE,
    );

    $this->setCacheBackend($cache_backend, 'layout_region');
    $this->alterInfo('layout_region');
  }

}
