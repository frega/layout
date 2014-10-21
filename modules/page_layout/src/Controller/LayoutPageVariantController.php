<?php

/**
 * @file
 * Contains \Drupal\page_manager\Controller\PageManagerController.
 */

namespace Drupal\page_layout\Controller;

use Drupal\block\BlockManagerInterface;
use Drupal\page_layout\LayoutStorageInterface;
Use Drupal\Core\Plugin\Context\ContextHandler;
use Drupal\page_manager\PageInterface;

use Drupal\Component\Serialization\Json;
use Drupal\Component\Utility\String;
use Drupal\Core\Controller\ControllerBase;

use Drupal\page_layout\Plugin\LayoutRegion\LayoutConfigurableRegionInterface;
use Drupal\page_layout\Plugin\LayoutRegion\LayoutConfigurableRegionBase;
use Drupal\page_layout\Plugin\LayoutRegion\LayoutRegionPluginManager;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides route controllers for Page Manager.
 */
class LayoutPageVariantController extends ControllerBase {
  /**
   * The block manager.
   *
   * @var \Drupal\block\BlockManagerInterface
   */
  protected $blockManager;

  /**
   * The context handler.
   *
   * @var \Drupal\Core\Plugin\Context\ContextHandler
   */
  protected $contextHandler;

  /**
   * The context handler.
   *
   * @var \Drupal\page_layout\Plugin\LayoutRegion\LayoutRegionPluginManager
   */
  protected $layoutRegionManager;

  /**
   * Constructs a new PageVariantEditForm.
   *
   * @param \Drupal\block\BlockManagerInterface $block_manager
   *   The block manager.
   * @param \Drupal\Core\Plugin\Context\ContextHandler $context_handler
   *   The context handler.
   */
  public function __construct(BlockManagerInterface $block_manager, ContextHandler $context_handler, LayoutRegionPluginManager $layoutRegionManager) {
    $this->blockManager = $block_manager;
    $this->contextHandler = $context_handler;
    $this->layoutRegionManager = $layoutRegionManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.block'),
      $container->get('context.handler'),
      $container->get('plugin.manager.page_layout.region')
    );
  }

  /**
   * Presents a list of blocks to add to the page variant.
   *
   * @param \Drupal\page_manager\PageInterface $layout
   *   The page entity.
   * @param string $layout_region_id
   *   The page variant ID.
   *
   * @return array
   *   The block selection page.
   */
  public function selectBlock(PageInterface $page, $page_variant_id, $layout_region_id = NULL) {
    // Add a section containing the available blocks to be added to the variant.
    $form = array(
      '#type' => 'container',
      '#attached' => array(
        'library' => array(
          'core/drupal.ajax',
        ),
      ),
    );

    // Sort the plugins first by category, then by label.
    $plugins = $this->blockManager->getDefinitionsForContexts($page->getContexts());

    foreach ($plugins as $plugin_id => $plugin_definition) {
      $category = String::checkPlain($plugin_definition['category']);
      $category_key = 'category-' . $category;
      if (!isset($form['place_blocks']['list'][$category_key])) {
        $form['place_blocks']['list'][$category_key] = array(
          '#type' => 'details',
          '#title' => $category,
          '#open' => TRUE,
          'content' => array(
            '#theme' => 'links',
            '#links' => array(),
            '#attributes' => array(
              'class' => array(
                'block-list',
              ),
            ),
          ),
        );
      }

      $form['place_blocks']['list'][$category_key]['content']['#links'][$plugin_id] = array(
        'title' => $plugin_definition['admin_label'],
        // path: '/admin/structure/page_manager/manage/{page}/manage/{page_variant_id}/layout/{layout_region_id}/block/{block_id}/add'
        'href' => '/admin/structure/page_manager/manage/' . $page->id() .'/manage/' . $page_variant_id . '/layout/'  . $layout_region_id . '/block/' . $plugin_id . '/add',
        'attributes' => array(
          'class' => array('use-ajax', 'block-filter-text-source'),
          'data-accepts' => 'application/vnd.drupal-modal',
          'data-dialog-options' => Json::encode(array(
              'width' => 700,
            )),
        ),
      );
    }

    return $form;
  }

  public function getAvailableBlocks($page) {
    uasort($plugins, function ($a, $b) {
      if ($a['category'] != $b['category']) {
        return strnatcasecmp($a['category'], $b['category']);
      }
      return strnatcasecmp($a['admin_label'], $b['admin_label']);
    });

    return $plugins;
  }

  /**
   * Presents a list of blocks to add to the page variant.
   *
   * @param \Drupal\page_manager\PageInterface $layout
   *   The page entity.
   * @param string $layout_region_id
   *   The page variant ID.
   *
   * @return array
   *   The block selection page.
   */
  public function selectRegion(PageInterface $page, $page_variant_id, $layout_region_id = NULL) {
    // Add a section containing the available blocks to be added to the variant.
    $form = array(
      '#type' => 'container',
      '#attached' => array(
        'library' => array(
          'core/drupal.ajax',
        ),
      ),
    );
    // Get the available layout region plugins
    $definitions = $this->layoutRegionManager->getDefinitions();
    foreach ($definitions as $plugin_id => $plugin_definition) {
      $plugin = $this->layoutRegionManager->createInstance($plugin_id, array());
      if (is_subclass_of($plugin, 'Drupal\page_layout\Plugin\LayoutRegion\LayoutConfigurableRegionInterface')) {
        $category = isset($plugin_definition['category']) ? String::checkPlain($plugin_definition['category']) : '';
        $category_key = 'category-' . $category;
        if (!isset($form['place_regions']['list'][$category_key])) {
          $form['place_regions']['list'][$category_key] = array(
            '#type' => 'details',
            '#title' => $category,
            '#open' => TRUE,
            'content' => array(
              '#theme' => 'links',
              '#links' => array(),
              '#attributes' => array(
                'class' => array(
                  'block-list',
                ),
              ),
            ),
          );
        }

        $label =
          isset($plugin_definition['admin_label']) ? $plugin_definition['admin_label'] :
            isset($plugin_definition['label']) ? $plugin_definition['label'] : $plugin_definition['id'];

        $form['place_regions']['list'][$category_key]['content']['#links'][$plugin_id] = array(
          'title' => $label,
          // path: '/admin/structure/page_manager/manage/{page}/manage/{page_variant_id}/layout/{layout_region_id}/block/{block_id}/add'
          'href' => '/admin/structure/page_manager/manage/' . $page->id() .'/manage/' . $page_variant_id . '/layout/'  . $layout_region_id . '/region/' . $plugin_id . '/add',
          'attributes' => array(
            'class' => array('use-ajax', 'block-filter-text-source'),
            'data-accepts' => 'application/vnd.drupal-modal',
            'data-dialog-options' => Json::encode(array(
                'width' => 700,
              )),
          ),
        );
      }
    }

    return $form;
  }

}
