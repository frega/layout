<?php

/**
 * @file
 * Contains \Drupal\page_manager\Plugin\PageVariant\LandingPageVariant.
 */

namespace Drupal\page_layout\Plugin\PageVariant;

use Drupal\block\BlockPluginInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\layout\Plugin\LayoutInterface;
use Drupal\page_layout\PageLayout;
use Drupal\page_layout\Plugin\LayoutPageVariantInterface;
use Drupal\page_manager\ContextHandler;
use Drupal\page_manager\Plugin\PageVariantBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Drupal\layout\Layout;
use Drupal\layout\Plugin\LayoutRegionPluginBag;


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
   * The context handler.
   *
   * @note: this is public, so that LayoutRegion/Layout instances
   * can access this; tbd if that stays.
   *
   * @var \Drupal\page_manager\ContextHandler
   */
  public $contextHandler;

  /**
   * The current user.
   *
   * @note: this is public, so that LayoutRegion/LayoutTemplate instances
   * can access this tbd if that stays.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  public $account;

  /**
   * Layout template.
   *
   * @var \Drupal\layout\Plugin\LayoutInterface
   */
  public $layout;

  /**
   * Layout regions.
   *
   * @var \Drupal\layout\Plugin\LayoutRegionPluginBag
   */
  public $layoutRegionBag;

  public function getContextHandler() {
    return $this->contextHandler;
  }

  /**
   * {@inheritdoc}
   */
  public function addLayoutRegion(array $configuration) {
    $configuration['uuid'] = $this->uuidGenerator()->generate();
    $this->getLayoutRegions()->addInstanceId($configuration['uuid'], $configuration);
    // @note: we need to update the configuration immediately to make sure this is persistable in the Page.
    $this->configuration['regions'] = $this->getLayoutRegions()->getConfiguration();
    return $configuration['uuid'];
  }

  /**
   * {@inheritdoc}
   */
  public function updateLayoutRegion($layout_region_id, array $configuration) {
    $existing_configuration = $this->getLayoutRegion($layout_region_id)->getConfiguration();
    $this->getLayoutRegions()->setInstanceConfiguration($layout_region_id, $configuration + $existing_configuration);
    // @note: we need to update the configuration immediately to make sure this is persistable in the Page.
    $this->configuration['regions'] = $this->getLayoutRegions()->getConfiguration();
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getLayoutRegion($layout_region_id) {
    $layoutRegion = $this->getLayoutRegions()->get($layout_region_id);
    // @todo: we need to get some kind of reference for the nested plugins, see views' PluginBase::init().
    if (!isset($layoutRegion->pageVariant)) {
      $layoutRegion->pageVariant = $this;
    }
    return $layoutRegion;
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
    // @note: we need to update the configuration immediately to make sure this is persistable in the Page.
    $this->configuration['regions'] = $this->getLayoutRegions()->getConfiguration();
    return $this;
  }

  /**
   * Initializes the page variant regions on the basis of given layout.
   *
   * @param LayoutInterface $layout
   * @return LayoutRegionPluginBag|\Drupal\page_layout\Plugin\LayoutRegionPluginBag
   */
  private function initializeLayoutRegionsFromLayout(LayoutInterface $layout) {
    $this->configuration['regions'] = array();
    $definitions = $layout ? $layout->getRegionDefinitions() : array();
    $weight = 0;
    foreach ($definitions as $id => $regionPluginDefinition) {
      $this->addLayoutRegion(array(
        'id' => !empty($regionPluginDefinition['plugin_id']) ? $regionPluginDefinition['plugin_id'] : 'default',
        'region_id' => $id,
        'label' => $regionPluginDefinition['label'],
        'weight' => $weight,
      ));
      $weight++;
    }
    $this->configuration['regions'] = $this->getLayoutRegions()->getConfiguration();
    return $this->getLayoutRegions();
  }

  /**
   * {@inheritdoc}
   */
  public function getLayoutRegions() {
    if (!isset($this->layoutRegionBag) || !$this->layoutRegionBag) {
      if (!isset($this->configuration['regions'])) {
        return $this->initializeLayoutRegionsFromLayout($this->getLayout());
      }

      $regions_data = $this->configuration['regions'];
      $this->layoutRegionBag = new LayoutRegionPluginBag(Layout::layoutRegionPluginManager(),
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
      $this->getLayout()->getLayoutRegionPluginDefinitions();
  }


  /**
   * {@inheritdoc}
   */
  public function getLayoutId() {
    return isset($this->configuration['layout']) ? $this->configuration['layout'] : NULL;
  }

  /**
   * Returns current Layout plugin instance.
   *
   * @todo: allow for configuration to be saved (not just the pluginId).
   *
   * @return \Drupal\page_layout\Plugin\LayoutPluginInterface
   */
  public function getLayout($reset = FALSE) {
    if (isset($this->layout) && !$reset) {
      return $this->layout;
    }
    $template_plugin_id = $this->getLayoutId();
    if (!$template_plugin_id) {
      throw new \Exception('Missing layout id');
    }

    $this->layout = Layout::layoutPluginManager()->createInstance($template_plugin_id);

    // @todo: we need to get some kind of reference for the nested plugins, see views' PluginBase::init().
    $this->layout->pageVariant = $this;
    return $this->layout;
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
    if ($this->getLayoutId() && $layout = $this->getLayout()) {
      $regions = $this->getLayoutRegions();
      $renderArray = array();
      $rootRegions = array();
      // Find rootRegions - @note we are doing it this way because *nesting* getLayoutRegions-calls
      // resets the internal iterator apparently.
      foreach ($regions as $region) {
        if (!$region->getParentRegionId()) {
          $rootRegions[] = $region;
        }
      }

      foreach ($rootRegions as $region) {
        $renderArray[] = $region->build($this);
      }

      return array(
        '#theme' => 'layout',
        '#regions' => $renderArray,
        // All layouts get a "Configure layout" contextual link.
        '#contextual_links' => array(
          'page_manager.page_variant_edit' => array(
            'route_parameters' => array('page' => '1', 'page_variant_id' => $this->id()),
          ),
        ),
      );
    }
    return array();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, array &$form_state) {
    // Adding
    $adding_variant = !isset($this->configuration['layout']);

    $form = parent::buildConfigurationForm($form, $form_state);
    $form['layout'] = array(
      '#title' => t('Layout template'),
      '#type' => 'select',
      '#default_value' => $this->getLayoutId(),
      '#options' => Layout::getLayoutOptions(),
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
        '#attached' => array(
          'library' => array(
            'page_layout/layout'
          ),
          'js' =>  array(
            array('data' => PageLayout::getLayoutPageVariantClientData($page, $page_variant), 'type' => 'setting')
          ),
        ),
      );
    }
    return $form;
  }

  public function submitConfigurationForm(array &$form, array &$form_state) {
    parent::submitConfigurationForm($form, $form_state);
    $this->configuration['layout'] = $form_state['values']['layout'];

    // @note: we have no "oop"-way to latch onto the Page-preSave hook.
    if (!isset($this->configuration['regions'])) {
      $this->configuration['regions'] = $this->getLayoutRegions()->getConfiguration();
    }
  }

}
