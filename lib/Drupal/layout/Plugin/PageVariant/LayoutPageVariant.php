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
use Drupal\layout\Plugin\LayoutRegionPluginBag;
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
  public function addLayoutRegion(array $configuration) {
    $configuration['uuid'] = $this->uuidGenerator()->generate();
    $this->getLayoutRegions()->addInstanceId($configuration['uuid'], $configuration);
    return $configuration['uuid'];
  }

  /**
   * {@inheritdoc}
   */
  public function getLayoutRegion($layout_region_id) {
    return $this->getLayoutRegions()->get($layout_region_id);
  }

  /**
   * {@inheritdoc}
   */
  public function removeLayoutRegion($layout_region_id) {
    $this->getLayoutRegions()->removeInstanceId($layout_region_id);
    // @todo: remove contained blocks.
    $blocksInRegion = $this->getBlockBag()->getAllByRegion($layout_region_id);
    foreach ($blocksInRegion as $block_id => $block) {
      $this->getBlockBag()->removeInstanceId($block_id);
    }
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getLayoutRegions() {
    if (!isset($this->layoutRegionBag) || !$this->layoutRegionBag) {
      if (!isset($this->configuration['regions'])) {
        $this->configuration['regions'] = array();
        $layoutTemplate = $this->getLayoutTemplate();
        $definitions = $layoutTemplate ? $layoutTemplate->getLayoutRegionPluginDefinitions() : array();
        foreach ($definitions as $nr => $regionPluginDefinition) {
          $this->addLayoutRegion(array(
            'id' => $regionPluginDefinition['plugin_id'],
            'label' => $regionPluginDefinition['label'],
            'weight' => $nr,
          ));
        }
        $this->configuration['regions'] = $this->getLayoutRegions()->getConfiguration();
        return $this->getLayoutRegions();
      }

      $regions_data = $this->configuration['regions'];
      $this->layoutRegionBag = new LayoutRegionPluginBag(\Drupal::service('plugin.manager.layout.layout_region'),
        $regions_data
      );
      $this->layoutRegionBag->sort();
    }
    return $this->layoutRegionBag;
  }

  /**
   * Build an array for region configuration.
   *
   * @todo: distinguish between "template" config & local overrides.
   */
  protected function getContainerConfiguration() {
    return isset($this->configuration['regions']) ? $this->configuration['regions'] :
      $this->getLayoutTemplate()->getLayoutRegionPluginDefinitions();
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
  public static function create(ContainerInterface $region, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $region->get('context.handler'),
      $region->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getRegionNames() {
    $regions = $this->getLayoutRegions();
    $names = array();
    foreach ($regions as $id => $region) {
      $names[$id] = $region->label();
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

      $form['blocks'] = array(
        '#title' => t('Blocks'),
        '#type' => 'textarea',
        // quick hack for styling.
        '#prefix' => '<div class="layout-configure-form">',
        '#suffix' => '</div>',
        '#default_value' => '',
        '#description' => t('Provide the JSON describing all blocks of this layout.'),
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
    if (!isset($this->configuration['regions'])) {
      $this->configuration['regions'] = $this->getLayoutRegions()->getConfiguration();
    }
  }

}
