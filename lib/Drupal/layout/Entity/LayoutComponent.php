<?php

/**
 * @file
 * Contains \Drupal\layout\Entity\LayoutComponent.
 */


namespace Drupal\layout\Entity;

use Drupal\Core\Cache\Cache;
use Drupal\block\Entity\Block;
use Drupal\block\BlockInterface;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Config\Entity\EntityWithPluginBagInterface;
use Drupal\Core\Entity\EntityStorageInterface;


/**
 * Defines a Layout component configuration entity class.
 *
 * @ConfigEntityType(
 *   id = "layout_component",
 *   label = @Translation("Layout Component"),
 *   controllers = {
 *     "access" = "Drupal\block\BlockAccessController",
 *     "view_builder" = "Drupal\block\BlockViewBuilder",
 *     "form" = {
 *       "default" = "Drupal\layout\LayoutComponentFormController",
 *       "delete"  = "Drupal\layout\Form\LayoutComponentDeleteConfirmForm"
 *     }
 *   },
 *   config_prefix = "layout_component",
 *   admin_permission = "administer layouts",
 *   fieldable = FALSE,
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label"
 *   }
 * )
 */

class LayoutComponent extends Block implements BlockInterface {
  /**
   * {@inheritdoc}
   */
  public function toArray() {
    $properties = parent::toArray();
    $names = array(
      'layout',
      'label',
      'theme',
      'container',
      'weight',
      'plugin',
      'settings',
      'visibility',
    );
    foreach ($names as $name) {
      $properties[$name] = $this->get($name);
    }

    $settings = $this->get('settings');
    $properties['label'] = isset($settings['label']) ? $settings['label'] : $this->id();

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    $this->theme = NULL;
    parent::calculateDependencies();
    // Remove the theme dependency, LayoutComponents are theme-agnostic.
    unset($this->dependencies['theme']);
    return $this->dependencies;
  }

  /**
   * @return Layout
   */
  public function getLayoutEntity() {
    return layout_load($this->get('layout'));
  }
}
