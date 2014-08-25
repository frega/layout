<?php

/**
 * @file
 * Contains \Drupal\page_manager\Plugin\PageVariant\LandingPageVariant.
 */

namespace Drupal\page_layout\Plugin\DisplayVariant;

use Drupal\block\BlockPluginInterface;
use Drupal\Component\Uuid\UuidInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\layout_plugin\LayoutRendererBlockAndContext;
use Drupal\layout_plugin\Plugin\Layout\LayoutInterface;
use Drupal\page_layout\PageLayout;
use Drupal\page_layout\Plugin\LayoutPageVariantInterface;
use Drupal\Core\Plugin\Context\ContextHandler;
use Drupal\Core\Display\VariantBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\page_manager\PageExecutable;
use Drupal\page_manager\Plugin\ConditionVariantTrait;
use Drupal\page_manager\Plugin\ContextAwareVariantInterface;
use Drupal\page_manager\Plugin\ContextAwareVariantTrait;
use Drupal\page_manager\Plugin\PageAwareVariantInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\layout_plugin\Layout;
use Drupal\layout_plugin\Plugin\LayoutRegion\LayoutRegionPluginBag;
use Drupal\page_manager\Plugin\BlockVariantTrait;


/**
 * Provides a page variant that serves as a landing page.
 *
 * @DisplayVariant(
 *   id = "layout_page_variant",
 *   admin_label = @Translation("Layout page")
 * )
 */
class LayoutPageVariant extends VariantBase implements ContextAwareVariantInterface, ContainerFactoryPluginInterface, PageAwareVariantInterface, LayoutPageVariantInterface {

  use BlockVariantTrait;
  use ContextAwareVariantTrait;
  use ConditionVariantTrait;

  /**
   * Layout template.
   *
   * @var \Drupal\layout_plugin\Plugin\Layout\LayoutInterface
   */
  public $layout;

  /**
   * Layout regions.
   *
   * @var \Drupal\layout_plugin\Plugin\LayoutRegion\LayoutRegionPluginBag
   */
  public $layoutRegionBag;

  /**
   * The context handler.
   *
   * @var \Drupal\Core\Plugin\Context\ContextHandlerInterface
   */
  protected $contextHandler;

  /**
   * The UUID generator.
   *
   * @var \Drupal\Component\Uuid\UuidInterface
   */
  protected $uuidGenerator;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $account;

  /**
   * The page executable.
   *
   * @var \Drupal\page_manager\PageExecutable
   */
  protected $executable;

  /**
   * @return \Drupal\page_manager\PageInterface
   */
  public function getPage() {
    return $this->executable->getPage();
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
    $uuids_by_region_id = array();
    foreach ($definitions as $region_id => $regionPluginDefinition) {
      $uuids_by_region_id[$region_id] = $this->addLayoutRegion(array(
        'id' => !empty($regionPluginDefinition['plugin_id']) ? $regionPluginDefinition['plugin_id'] : 'default',
        'region_id' => $region_id,
        'label' => $regionPluginDefinition['label'],
        'weight' => $weight,
        'parent' => isset($regionPluginDefinition['parent']) ? $regionPluginDefinition['parent'] : NULL,
        'options' => isset($regionPluginDefinition['options']) ? $regionPluginDefinition['options'] : NULL,
      ));
      $weight++;
    }

    $configuration = $this->getLayoutRegions()->getConfiguration();
    // Make sure that parent machine names are replaced with uuids.
    // We run this *after* so that we can be sure to have generated all
    // regions.
    foreach ($configuration as $uuid => $region_config) {
      if (isset($region_config['parent'])) {
        $region_config['parent'] = $uuids_by_region_id[$region_config['parent']];
        $this->updateLayoutRegion($uuid, $region_config);
      }
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
   * @return \Drupal\layout_plugin\Plugin\Layout\LayoutInterface
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
   * Constructs a new LayoutPageVariant.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Plugin\Context\ContextHandler $context_handler
   *   The context handler.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The current user.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ContextHandler $context_handler, AccountInterface $account, UuidInterface $uuid_generator) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->contextHandler = $context_handler;
    $this->account = $account;
    $this->uuidGenerator = $uuid_generator;
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
      $container->get('current_user'),
      $container->get('uuid')
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
    return $this->build();
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    if ($this->getLayoutId() && $layout = $this->getLayout()) {
      $renderer = new LayoutRendererBlockAndContext($this->contextHandler, $this->account);
      $output = $renderer->build($layout, $this);
      return $output;
    }
    return array();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    // Adding
    $adding_variant = !isset($this->configuration['layout']);

    $form = parent::buildConfigurationForm($form, $form_state);
    $form['layout'] = array(
      '#title' => t('Layout'),
      '#type' => 'select',
      '#default_value' => $this->getLayoutId(),
      '#options' => Layout::getLayoutOptions(),
      '#disabled' => !$adding_variant,
      '#description' => t('Note: change a template would require salvaging blocks from disappearing regions. We will do that ... soon.'),
      '#required' => TRUE,
    );

    $page = $form_state['build_info']['args'][0];

    if (!$adding_variant) {
      $page_variant = $page->getVariant($form_state['build_info']['args'][1]);
      $page_variant->init($page->getExecutable());

      $form['links'] = array(
        '#type' => 'markup',
        '#markup' => l(t('Preview layout'), $page->get('path'), array('attributes' => array('target' => drupal_html_id($page->id()))))
      );

      // This is just a quick hack, we need some form of theme_layout_ui call.
      $form['blocks'] = array(
        '#title' => t('Blocks'),
        '#markup' =>
          '<label>' . t('Layout UI') . '</label>' .
          '<div class="layout-configure-form">' .
            '<div id="layout-app">' .
              '<div class="operations">' .
                '<a class="highlight-blocks" href="#blocks">' . $this->t('Focus on blocks') . '</a> ' .
                '<a class="highlight-regions" href="#regions">' . $this->t('Focus on regions') . '</a> ' .
              '</div>' .
              '<div class="layout-app-inner"></div>' .
            '</div>' .
          '</div>',
        '#default_value' => '',
        '#attached' => array(
          'library' => array(
            'page_layout/layout'
          ),
          'js' =>  array(
            array('data' => PageLayout::getLayoutPageVariantClientData($page_variant), 'type' => 'setting')
          ),
        ),
      );
    }
    return $form;
  }

  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);
    $this->configuration['layout'] = $form_state['values']['layout'];

    // @note: we have no "oop"-way to latch onto the Page-preSave hook.
    if (!isset($this->configuration['regions'])) {
      $this->configuration['regions'] = $this->getLayoutRegions()->getConfiguration();
    }
  }

  public function init(PageExecutable $executable) {
    return $this->setExecutable($executable);
  }

  public function getContexts() {
    return $this->executable->getContexts();
  }

  /**
   * {@inheritdoc}
   */
  protected function contextHandler() {
    return $this->contextHandler;
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    return array(
      'selection_conditions' => $this->getSelectionConditions()->getConfiguration(),
      'blocks' => $this->getBlockBag()->getConfiguration(),
    ) + parent::getConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function getSelectionLogic() {
    return $this->configuration['selection_logic'];
  }

  /**
   * {@inheritdoc}
   */
  protected function getSelectionConfiguration() {
    return $this->configuration['selection_conditions'];
  }

  /**
   * {@inheritdoc}
   */
  public function setExecutable(PageExecutable $executable) {
    $this->executable = $executable;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  protected function getBlockConfig() {
    return $this->configuration['blocks'];
  }

  /**
   * {@inheritdoc}
   */
  protected function uuidGenerator() {
    return $this->uuidGenerator;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return parent::defaultConfiguration() + array(
      'blocks' => array(),
      'selection_conditions' => array(),
      'selection_logic' => 'and',
    );
  }
}
