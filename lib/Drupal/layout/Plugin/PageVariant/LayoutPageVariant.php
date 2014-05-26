<?php

/**
 * @file
 * Contains \Drupal\page_manager\Plugin\PageVariant\LandingPageVariant.
 */

namespace Drupal\layout\Plugin\PageVariant;

use Drupal\block\BlockPluginInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\layout\Layouts;
use Drupal\layout\Plugin\LayoutContainerPluginBag;
use Drupal\layout\Plugin\LayoutPageVariantInterface;
use Drupal\page_manager\ContextHandler;
use Drupal\page_manager\PageInterface;
use Drupal\page_manager\Plugin\PageVariantBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a page variant that serves as a landing page.
 *
 * @PageVariant(
 *   id = "layout_page_variant",
 *   admin_label = @Translation("Layout page")
 * )
 */
class LayoutPageVariant extends PageVariantBase implements LayoutPageVariantInterface, ContainerFactoryPluginInterface {
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
    // @todo: remove contained blocks.
    $blocksInRegion = $this->getBlockBag()->getAllByRegion($layout_container_id);
    foreach ($blocksInRegion as $block_id => $block) {
      $this->getBlockBag()->removeInstanceId($block_id);
    }
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getLayoutContainers() {
    if (!isset($this->layoutContainerBag) || !$this->layoutContainerBag) {
      if (!isset($this->configuration['containers'])) {
        $this->configuration['containers'] = array();
        $layoutTemplate = $this->getLayoutTemplate();
        $definitions = $layoutTemplate ? $layoutTemplate->getLayoutContainerPluginDefinitions() : array();
        foreach ($definitions as $nr => $containerPluginDefinition) {
          $this->addLayoutContainer(array(
            'id' => $containerPluginDefinition['plugin_id'],
            'label' => $containerPluginDefinition['label'],
            'weight' => $nr,
          ));
        }
        $this->configuration['containers'] = $this->getLayoutContainers()->getConfiguration();
        return $this->getLayoutContainers();
      }

      $containers_data = $this->configuration['containers'];
      $this->layoutContainerBag = new LayoutContainerPluginBag(\Drupal::service('plugin.manager.layout.layout_container'),
        $containers_data
      );
      $this->layoutContainerBag->sort();
    }
    return $this->layoutContainerBag;
  }

  /**
   * Build an array for container configuration.
   *
   * @todo: distinguish between "template" config & local overrides.
   */
  protected function getContainerConfiguration() {
    return isset($this->configuration['containers']) ? $this->configuration['containers'] :
      $this->getLayoutTemplate()->getLayoutContainerPluginDefinitions();
  }


  /**
   * {@inheritdoc}
   */
  public function getLayoutTemplateId() {
    return isset($this->configuration['template_id']) ? $this->configuration['template_id'] : NULL;
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
    if (!$template_plugin_id) {
      throw new Exception('Missing layout template plugin id');
    }

    $layoutTemplateManager = \Drupal::service('plugin.manager.layout.layout_template');
    return $this->template_plugin = $layoutTemplateManager->createInstance($template_plugin_id, $this->configuration);
  }

  /**
   * Returns all block plugin instances in given region.
   *
   * @param $region_id
   * @return BlockPluginInterface[]
   */
  public function getBlocksByRegion($region_id) {
    $all_by_region = $this->getBlockBag()->getAllByRegion($region_id);
    return isset($all_by_region[$region_id]) ? $all_by_region[$region_id] : array();
  }

  /**
   * Remove a block.
   *
   * @note: this is currently missing in PageVariant, refactor up the chain.
   *
   * @param $block_id
   * @return $this
   */
  public function removeBlock($block_id) {
    $this->getBlockBag()->removeInstanceId($block_id);
    return $this;
  }


  /**
   * Constructs a new BlockPageVariant.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\page_manager\ContextHandler $context_handler
   *   The context handler.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The current user.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ContextHandler $context_handler, AccountInterface $account) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->contextHandler = $context_handler;
    $this->account = $account;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('context.handler'),
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getRegionNames() {
    $containers = $this->getLayoutContainers();
    $names = array();
    foreach ($containers as $id => $container) {
      $names[$id] = $container->label();
    }
    return $names;
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    if ($this->getLayoutTemplateId() && $layout_template = $this->getLayoutTemplate()) {
      return $layout_template->build($this);
    }
    return array();
  }

  public function buildConfigurationForm(array $form, array &$form_state) {
    // Adding
    $adding_variant = !isset($this->configuration['template_id']);

    $form = parent::buildConfigurationForm($form, $form_state);
    $form['template_id'] = array(
      '#title' => t('Layout template'),
      '#type' => 'select',
      '#default_value' => $this->getLayoutTemplateId(),
      '#options' => Layouts::getLayoutTemplateOptions(),
      '#disabled' => !$adding_variant,
      '#description' => t('Note: change a template would require salvaging blocks from disappearing regions. We will do that ... soon.'),
      '#required' => TRUE,
    );

    $page = $form_state['build_info']['args'][0];

    if (!$adding_variant) {
      $page_variant = $page->getPageVariant($form_state['build_info']['args'][1]);

      $form['links'] = array(
        '#type' => 'markup',
        '#markup' => l(t('Preview layout'), $page->get('path'), array('attributes' => array('target' => drupal_html_id($page->id()))))
      );

      $form['components'] = array(
        '#title' => t('Components'),
        '#type' => 'textarea',
        // quick hack for styling.
        '#prefix' => '<div class="layout-configure-form">',
        '#suffix' => '</div>',
        '#default_value' => '',
        '#description' => t('Provide the JSON describing all components of this layout.'),
        '#attached' => array(
          'library' => array(
            'layout/layout'
          ),
          'js' =>  array(
            array('data' => Layouts::getLayoutPageVariantClientData($page, $page_variant), 'type' => 'setting')
          ),
        ),
      );
    }
    return $form;
  }

  public function alterConfigurationForm(array &$form, array &$form_state, $form_id, PageInterface $page) {
    $form['block_section']['#access'] = FALSE;
    $form['selection_section']['#open'] = FALSE;
    return $form;
  }


  public function submitConfigurationForm(array &$form, array &$form_state) {
    parent::submitConfigurationForm($form, $form_state);
    $this->configuration['template_id'] = $form_state['values']['template_id'];

    // @note: we have no "oop"-way to latch onto the Page-preSave hook.
    if (!isset($this->configuration['containers'])) {
      $this->configuration['containers'] = $this->getLayoutContainers()->getConfiguration();
    }
  }

}
