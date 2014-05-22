<?php

/**
 * @file
 * Definition of Drupal\layout\Entity\Layout.
 */

namespace Drupal\layout\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\EntityWithPluginBagsInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Component\Serialization\Json;

use Drupal\layout\Layouts;
use Drupal\layout\Plugin\LayoutContainerPluginBag;

use Drupal\layout\LayoutStorageInterface;

/**
 * Defines a Layout configuration entity class.
 *
 * @ConfigEntityType(
 *   id = "layout",
 *   label = @Translation("Layout"),
 *   controllers = {
 *     "access" = "Drupal\layout\LayoutAccessController",
 *     "form" = {
 *       "add" = "Drupal\layout\Form\LayoutForm",
 *       "edit" = "Drupal\layout\Form\LayoutForm",
 *       "configure" = "Drupal\layout\Form\LayoutConfigureForm",
 *       "configure_containers" = "Drupal\layout\Form\LayoutConfigureContainersForm",
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
 *     "add-form" = "layout.layout_add",
 *     "edit-form" = "layout.layout_edit",
 *     "configure-containers-form" = "layout.layout_containers",
 *     "configure-form" = "layout.layout_configure",
 *     "delete-form" = "layout.layout_delete"
 *   }
 * )
 */
class Layout extends ConfigEntityBase implements LayoutStorageInterface, EntityWithPluginBagsInterface {
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
   * Template configuration for a LayoutTemplate plugin.
   *
   * @var array
   */
  public $template = array();

  /**
   * Container configuration
   *
   * @var array
   */
  public $containers = array();


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
   * @todo: this needs a) to be refactored into LayoutExecutable and b) we should
   *  delegate to the container and component plugins.
   */
  public function build() {
    $componentsByContainer = $this->getSortedComponentsByContainer();
    $contentByContainer = array();
    $entity_manager = \Drupal::entityManager();
    $containers = $this->getLayoutContainers();
    // @todo: refactor into container plugin.
    foreach ($containers as $container_id => $container) {
      $componentsRenderArray = array();
      if (isset($componentsByContainer[$container_id])) {
        foreach ($componentsByContainer[$container_id] as $component_id => $component) {
          $componentsRenderArray[] = array(
            '#theme' => 'layout_component',
            '#component' => $entity_manager->getViewBuilder('layout_component')->view($component),
          );
        }
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

  function getSortedComponents() {
    $components = $this->getComponents();
    uasort($components, function($a, $b) {
      $a_weight = $a->get('weight');
      $b_weight = $b->get('weight');
      if ($a_weight == $b_weight) {
        return 0;
      }
      return ($a_weight < $b_weight) ? -1 : 1;
    });
    return $components;
  }


  /**
   * Returns an array of container info
   * Keys: id and label.
   *
   * @return mixed
   */
  function getContainerArray() {
    return $this->getLayoutContainers()->getConfiguration();
  }

  /**
   * Return container info
   *
   * @param $container_id
   * @return null
   */
  function getContainer($container_id) {
    return $this->getLayoutContainer($container_id);
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
      $components = $this->getSortedComponents();
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
    $containers = $this->getLayoutContainers()->sort();

    $data = array(
      'id' => $this->id(),
      'layout' => isset($this->layout) ? $this->layout : ''
    );

    foreach ($containers as $container_id => $container) {
      $region_data = array(
        'id' => $container_id,
        'label' => $container->label(),
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

  /**
   * Returns the id of the LayoutTemplate.
   *
   * @todo: allow for configuration to be saved (not just the pluginId).
   *
   * @return string|null
   */
  public function getLayoutTemplateId() {
    return $this->get('template');
  }

  /**
   * Returns current LayoutTemplate plugin instance.
   *
   * @todo: allow for configuration to be saved (not just the pluginId).
   *
   * @return \Drupal\layout\Plugin\LayoutTemplatePluginInterface
   */
  public function getLayoutTemplate($reset = FALSE) {
    if (isset($this->template_plugin) && !$reset) {
      return $this->template_plugin;
    }
    $template_plugin_id = $this->getLayoutTemplateId();
    $layoutTemplateManager = \Drupal::service('plugin.manager.layout.layout_template');
    return $this->template_plugin = $layoutTemplateManager->createInstance($template_plugin_id, array());
  }

  /**
   * Returns (nested) array of template options (grouped by category).
   *
   * @return array
   */
  public function getLayoutTemplateOptions() {
    $layoutTemplateManager = \Drupal::service('plugin.manager.layout.layout_template');
    // Sort the plugins first by category, then by label.
    $plugins = $layoutTemplateManager->getDefinitions();
    $options = array();
    foreach ($plugins as $id => $plugin) {
      $category = isset($plugin['category']) ? $plugin['category'] : 'default';
      if (!isset($options[$category])) {
        $options[$category] = array();
      }
      $options[$category][$id] = $plugin['title'];
    }
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function addLayoutContainer(array $configuration) {
    $configuration['uuid'] = $this->uuidGenerator()->generate();
    $this->getLayoutContainers()->addInstanceId($configuration['uuid'], $configuration);
    return $configuration['uuid'];
  }

  /**
   * {@inheritdoc}
   */
  public function getLayoutContainer($layout_container_id) {
    return $this->getLayoutContainers()->get($layout_container_id);
  }

  /**
   * {@inheritdoc}
   */
  public function removeLayoutContainer($layout_container_id) {
    $this->getLayoutContainers()->removeInstanceId($layout_container_id);
    // @todo: refactor this, convert this to a component plugin bag anyway.
    // remove all components in that container.
    $components = $this->getSortedComponentsByContainer($layout_container_id);
    foreach ($components as $component) {
      $component->delete();
    }

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getLayoutContainers() {
    if (!isset($this->layoutContainerBag) || !$this->layoutContainerBag) {
      $containers_data = $this->get('containers');
      $this->layoutContainerBag = new LayoutContainerPluginBag(\Drupal::service('plugin.manager.layout.layout_container'),
        $containers_data
      );
      $this->layoutContainerBag->sort();
    }
    return $this->layoutContainerBag;
  }

  /**
   * Gets an executable instance for this layout.
   *
   * @return \Drupal\layout\LayoutExecutable
   *   A layout executable instance.
   */
  public function getExecutable() {
    // Ensure that an executable View is available.
    if (!isset($this->executable)) {
      $this->executable = Layouts::executableFactory()->get($this);
    }

    return $this->executable;
  }

  /**
   * {inheritdoc}
   */
  public function calculateDependencies() {
    parent::calculateDependencies();
    $components = $this->getComponents();
    foreach ($components as $component) {
      $this->addDependency('entity', $component->id());
    }
    // @todo: add plugin dependencies for containers.
  }

  /**
   * {inheritdoc}
   */
  public function getPluginBags() {
    return array(
      'containers' => $this->getLayoutContainers()
    );
  }

  /**
   * Wraps the route builder.
   *
   * @return \Drupal\Core\Routing\RouteBuilderInterface
   *   An object for state storage.
   */
  protected function routeBuilder() {
    return \Drupal::service('router.builder');
  }

  public function preSave(EntityStorageInterface $storage) {
    // @note: plugin bags will be taken courtesy of EntityWithPluginBagsInterface.

    // Ensure that the layout containers are created as per chosen LayoutTemplate.
    if ($this->isNew()) {
      $layoutTemplate = $this->getLayoutTemplate();
      foreach ($layoutTemplate->getLayoutContainerPluginDefinitions() as $nr => $containerPluginDefinition) {
        $this->addLayoutContainer(array(
          'id' => $containerPluginDefinition['plugin_id'],
          'label' => $containerPluginDefinition['label'],
          'weight' => $nr,
        ));
      }
    } else {
      // Reassign & regenerate container plugins on Template change
      // @todo: this needs to be refactored elsewhere (separate UI as well).
      if ($this->original->getLayoutTemplateId() != $this->getLayoutTemplateId()) {
        // Remove all "old" and add new ...
        $containers = $this->original->getLayoutContainers();
        foreach ($containers as $container) {
          $this->removeLayoutContainer($container->id());
        }
        // Reset template plugin
        $layoutTemplate = $this->getLayoutTemplate(TRUE);
        foreach ($layoutTemplate->getLayoutContainerPluginDefinitions() as $nr => $containerPluginDefinition) {
          $this->addLayoutContainer(array(
            'id' => $containerPluginDefinition['plugin_id'],
            'label' => $containerPluginDefinition['label'],
            'weight' => $nr,
          ));
        }
      }
    }

    return parent::preSave($storage);
  }

  public function postSave(EntityStorageInterface $storage, $update = TRUE) {
    // @todo: if we change the path, we need to rebuild the route!
    $this->routeBuilder()->setRebuildNeeded();
    return parent::postSave($storage, $update);
  }

  public static function postDelete(EntityStorageInterface $storage, array $entities) {
    // @todo: cleanup - remove all *contained* layout_container and layout_component entities referencing this
    // layout entity.

    foreach ($entities as $layout) {
      $components = $layout->getComponents();
      foreach ($components as $component) {
        $component->delete();
      }
    }

    return parent::postDelete($storage, $entities);
  }
}
