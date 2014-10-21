<?php

/**
 * @file
 * Contains \Drupal\block_page\Form\PageVariantAddForm.
 */

namespace Drupal\page_layout\Form;

use Drupal\page_layout\Plugin\LayoutRegion\LayoutRegionPluginManager;
use Drupal\page_layout\Form\LayoutRegionFormBase;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for adding a new page variant.
 */
class LayoutRegionEditForm extends LayoutRegionFormBase {

  /**
   * The page variant manager.
   *
   * @var \Drupal\page_layout\Plugin\LayoutPluginManager
   */
  protected $layoutPluginManager;

  /**
   * Constructs a new PageVariantAddForm.
   *
   * @param \Drupal\page_layout\Plugin\LayoutPluginManager $layout_plugin_manager
   *   The layout manager.
   */
  public function __construct(LayoutRegionPluginManager $layout_plugin_manager) {
    $this->layoutPluginManager = $layout_plugin_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.page_layout.region')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'layout_layout_region_edit_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function submitText() {
    return $this->t('Edit layout region');
  }

  /**
   * {@inheritdoc}
   */
  protected function prepareLayoutRegion($region_id = 'default') {
    // Load the page variant directly from the block page.
    return $this->pageVariant->getLayoutRegion($region_id);
  }

}
