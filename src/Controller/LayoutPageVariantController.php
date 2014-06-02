<?php

/**
 * @file
 * Contains \Drupal\page_manager\Controller\PageManagerController.
 */

namespace Drupal\layout\Controller;

use Drupal\block\BlockManagerInterface;
use Drupal\layout\LayoutStorageInterface;

Use Drupal\page_manager\ContextHandler;

use Drupal\Component\Serialization\Json;
use Drupal\Component\Utility\String;
use Drupal\Core\Controller\ControllerBase;
use Drupal\page_manager\PageInterface;
use Drupal\page_manager\Plugin\PageVariantInterface;
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
   * @var \Drupal\page_manager\ContextHandler
   */
  protected $contextHandler;

  /**
   * Constructs a new PageVariantEditForm.
   *
   * @param \Drupal\block\BlockManagerInterface $block_manager
   *   The block manager.
   * @param \Drupal\page_manager\ContextHandler $context_handler
   *   The context handler.
   */
  public function __construct(BlockManagerInterface $block_manager, ContextHandler $context_handler) {
    $this->blockManager = $block_manager;
    $this->contextHandler = $context_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.block'),
      $container->get('context.handler')
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
    $build = array(
      '#type' => 'container',
      '#attached' => array(
        'library' => array(
          'core/drupal.ajax',
        ),
      ),
    );

    // Sort the plugins first by category, then by label.
    $plugins = $this->contextHandler->getAvailablePlugins($page->getContexts(), $this->blockManager);
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

}
