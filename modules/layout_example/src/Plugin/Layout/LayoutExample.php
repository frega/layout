<?php

namespace Drupal\layout_example\Plugin\Layout;
use Drupal\layout\Plugin\Layout\LayoutBase;

/**
 * The plugin that handles the default layout template.
 *
 * @ingroup layout_template_plugins
 *
 * @Layout(
 *   id = "layout_example",
 *   title = @Translation("Example Layout"),
 *   help = @Translation("Layout"),
 *   theme = "layout_example",
 *   derivative = "\Drupal\layout_example\Plugin\Layout\Deriver\LayoutExampleDeriver"
 * )
 */
class LayoutExample extends LayoutBase {
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
