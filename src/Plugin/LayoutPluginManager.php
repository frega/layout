<?php

/**
 * @file
 * Contains \Drupal\layout\Plugin\LayoutPluginManager.
 */

namespace Drupal\layout\Plugin;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Language\LanguageManager;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Plugin type manager for all layouts.
 */
class LayoutPluginManager extends DefaultPluginManager {

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
   * @param \Drupal\Core\Language\LanguageManager $language_manager
   *   The language manager.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, LanguageManager $language_manager, ModuleHandlerInterface $module_handler) {
    $plugin_definition_annotation_name = 'Drupal\layout\Annotation\Layout';
    parent::__construct("Plugin/Layout", $namespaces, $module_handler, $plugin_definition_annotation_name);

    $this->defaults += array(
      'plugin_type' => 'Layout',
      'register_theme' => TRUE,
    );

    $this->setCacheBackend($cache_backend, $language_manager, 'layout');
    $this->alterInfo('layout');
  }

}
