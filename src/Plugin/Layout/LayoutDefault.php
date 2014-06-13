<?php

/**
 * @file
 * Contains \Drupal\layout\Plugin\Layout\LayoutDefault.
 */

namespace Drupal\layout\Plugin\Layout;


/**
 * Provides a default class for Layout plugins.
 */
class LayoutDefault extends LayoutBase {

  function getBasePath() {
    $path = isset($this->pluginDefinition['path']) && $this->pluginDefinition['path'] ? $this->pluginDefinition['path'] : FALSE;
    return $path ? $path : '';
  }

  function getPreviewImagePath() {
    return isset($this->pluginDefinition['image']) && $this->pluginDefinition['image'] ? $this->getBasePath() . '/' . $this->pluginDefinition['image'] : FALSE;
  }

  function getCssFilename() {
    $module_path = drupal_get_path('module', $this->pluginDefinition['provider']);
    return isset($this->pluginDefinition['css']) && $this->pluginDefinition['css'] ? $module_path . '/' . $this->pluginDefinition['css'] : FALSE;
  }
}
