<?php

/**
 * @file
 * Contains \Drupal\layout_example\Plugin\Layout\Deriver\LayoutExampleDeriver.
 */

namespace Drupal\layout_example\Plugin\Layout\Deriver;

use Drupal\Component\Plugin\Derivative\DerivativeBase;
use Drupal\Component\Serialization\Yaml;

class LayoutExampleDeriver extends DerivativeBase {
  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $layouts = Yaml::decode(file_get_contents(drupal_get_path('module', 'layout_example'). '/layout_example.layouts.yml'));

    foreach ($layouts as $id => $layout) {
      $this->derivatives[$id] = array(
        'category' => $layout['category'],
        'theme' => isset($layout['theme']) ? $layout['theme'] : 'layout_example',
        'regions' => isset($layout['regions']) ? $layout['regions'] : array(),
        'label' => $layout['label'],
      ) + $base_plugin_definition;
    }

    return $this->derivatives;
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinition($derivative_id, $base_plugin_definition) {
    if (!empty($this->derivatives) && !empty($this->derivatives[$derivative_id])) {
      return $this->derivatives[$derivative_id];
    }
    $this->getDerivativeDefinitions($base_plugin_definition);
    return $this->derivatives[$derivative_id];
  }
}
