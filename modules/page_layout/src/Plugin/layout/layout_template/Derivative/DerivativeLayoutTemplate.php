<?php

/**
 * @file
 * Contains \Drupal\page_layout\Plugin\layout_template\Derivative\Example.
 */

namespace Drupal\page_layout\Plugin\layout\layout_template\Derivative;
use Drupal\Component\Plugin\Derivative\DerivativeBase;

use Drupal\Component\Serialization\Json;
use Drupal\Component\Plugin\Derivative\DerivativeInterface;

class DerivativeLayoutTemplate extends DerivativeBase {
  public function getDerivativeDefinitions($base_plugin_definition) {
    // This is just a test ... learning derivatives ...
    $example_file = dirname(__FILE__). '/templates.json';
    $content = file_get_contents($example_file);
    $derived = Json::decode($content);
    foreach ($derived as $d) {
      $key = $d['id'];
      $this->derivatives[$key] = array(
        'category' => $d['category'],
        'theme' => isset($d['theme']) ? $d['theme'] : 'layout_template',
        'regions' => isset($d['regions']) ? $d['regions'] : array(),
        'label' => $d['label'],
        'title' => $d['label'],
      ) + $base_plugin_definition;
    }
    return $this->derivatives;
  }

  public function getDerivativeDefinition($derivative_id, $base_plugin_definition) {
    if (!empty($this->derivatives) && !empty($this->derivatives[$derivative_id])) {
      return $this->derivatives[$derivative_id];
    }
    $this->getDerivativeDefinitions($base_plugin_definition);
    return $this->derivatives[$derivative_id];
  }

}
