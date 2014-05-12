<?php

/**
 * @file
 * Definition of Drupal\layout\Entity\Layout.
 */

namespace Drupal\layout\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use \Drupal\Component\Serialization\Json;


/**
 * Defines a Layout configuration entity class.
 *
 * @ConfigEntityType(
 *   id = "layout",
 *   label = @Translation("Layout"),
 *   controllers = {
 *     "access" = "Drupal\layout\LayoutAccessController",
 *     "form" = {
 *       "add" = "Drupal\layout\LayoutFormController",
 *       "edit" = "Drupal\layout\LayoutFormController",
 *       "configure" = "Drupal\layout\Form\LayoutConfigureForm",
 *       "delete_confirm" = "Drupal\layout\Form\LayoutDeleteConfirmForm",
 *       "list_components" = "Drupal\layout\Form\LayoutListComponentsForm"
 *     },
 *     "list_builder" = "Drupal\layout\LayoutListBuilder"
 *   },
 *   admin_permission = "administer layouts",
 *   config_prefix = "layout",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label"
 *   },
 *   links = {
 *     "add-form" = "layout.add",
 *     "edit-form" = "layout.edit",
 *     "configure-form" = "layout.configure",
 *     "delete-form" = "layout.delete_confirm"
 *   }
 * )
 */
class Layout extends ConfigEntityBase {
  /**
   * The unique ID of the layout.
   *
   * @var string
   */
  public $id = NULL;

  /**
   * The label of the layout.
   */
  public $label;

  /**
   * The administrative label of the layout.
   */
  public $admin_label;

  /**
   * Path at which this page layout can be viewed
   *
   * @todo: this is just a placeholder.
   *
   * @var string
   */
  public $path;

  /**
   * A brief description of this layout.
   *
   * @var string
   */
  public $description;

  /**
   * A template
   *
   * @var string
   */
  public $template;

  /**
   * A containers
   *
   * @var string
   */
  public $containers = '';


  protected $_componentsByContainer = NULL;

  /**
   * @todo: this is copied from NodeType, probably makes sense here, too.
   *
   * @return bool
   */
  function isLocked() {
    return FALSE;
  }

  /**
   * Builds and returns the renderable array for this block plugin.
   *
   * @return array
   *   A renderable array representing the content of the block.
   *
   */
  public function build() {
    $componentsByContainer = $this->getSortedComponentsByContainer();
    $contentByContainer = array();
    $entity_manager = \Drupal::entityManager();
    foreach ($componentsByContainer as $container_id => $components) {
      $componentsRenderArray = array();
      foreach ($components as $component_id => $component) {
        $componentsRenderArray[] = array(
          '#theme' => 'layout_component',
          '#component' => $entity_manager->getViewBuilder('layout_component')->view($component),
        );
      }
      $contentByContainer[] = array(
        '#theme' => 'layout_container',
        '#components' => $componentsRenderArray,
        '#container_id' => $container_id
      );
    }

    return array(
      '#theme' => 'layout',
      '#containers' => $contentByContainer
    );
  }

  /**
   * @return LayoutComponent[]
   */
  function getComponents() {
    // Get all the components which are in this region.
    $query = \Drupal::service('entity.manager')->getStorage('layout_component')->getQuery();
    $query->condition('layout', $this->id(), 'CONTAINS');
    $component_ids = $query->execute();

    $array = array();
    foreach ($component_ids as $component_id) {
      $component = layout_component_load($component_id);
      $array[$component->id()] = $component;
    }
    return $array;
  }

  /**
   * Returns an array of container info
   * Keys: id and label.
   *
   * @return mixed
   */
  function getContainerArray() {
    return json_decode($this->containers, TRUE);
  }

  /**
   * Return container info
   *
   * @param $container_id
   * @return null
   */
  function getContainer($container_id) {
    $containers = $this->getContainerArray();
    foreach ($containers as $container) {
      if ($container['id'] === $container_id) {
        return $container;
      }
    }
    return NULL;
  }

  /**
   * Retrieves all components in given container.
   *
   * @param null $container_id
   * @param bool $reset
   * @return array|null
   */
  function getSortedComponentsByContainer($container_id = NULL, $reset = FALSE) {
    if (!isset($this->_componentsByContainer) || $reset) {
      $this->_componentsByContainer = array();
      $components = $this->getComponents();
      foreach ($components as $component_id => $component) {
        if (!isset($this->_componentsByContainer[$component->get('container')])) {
          $this->_componentsByContainer[$component->get('container')] = array();
        }
        $this->_componentsByContainer[$component->get('container')][$component_id] = $component;
      }
    }

    if (!isset($container_id)) {
      return $this->_componentsByContainer;
    }

    return isset($this->_componentsByContainer[$container_id]) ? $this->_componentsByContainer[$container_id] : array();
  }

  /**
   * Returns an array representation grouped for json-serialisation.
   * @todo: this is a bad name (and should probably change)
   *
   * @return array
   */
  public function exportGroupedByContainer() {
    // Render the layout in an admin context with region demonstrations.
    $containers = $this->getContainerArray();
    $data = array(
      'id' => $this->id(),
      'layout' => isset($this->layout) ? $this->layout : ''
    );

    foreach ($containers as $info) {
      $container_id = $info['id'];
      $region_data = array(
        'id' => $container_id,
        'label' => isset($info['label']) ? $info['label'] : $container_id,
        'components' => array(),
      );
      $components = $this->getSortedComponentsByContainer($container_id);
      foreach ($components as $component_id => $component) {
        $component_info = $component->toArray();
        // @todo: this should be proper data. Component instances should maybe
        // be classed objects as well.
        $block_id = str_replace('component.', '', $component_id);
        $region_data['components'][] = $component_info;
      }
      $data['containers'][] = $region_data;
    }

    return $data;
  }

  public function preSave(EntityStorageInterface $storage) {
    // @todo: if we change the path, we need to rebuild the route!
    return parent::preSave($storage);
  }

  public function postSave(EntityStorageInterface $storage, $update = TRUE) {
    // @todo: if we change the path, we need to rebuild the route!
    return parent::postSave($storage, $update);
  }

  public static function postDelete(EntityStorageInterface $storage, array $entities) {
    // @todo: cleanup - remove all *contained* layout_container and layout_component entities referencing this
    // layout entity.
    return parent::postDelete($storage, $entities);
  }
}
