<?php

/**
 * @file
 * Contains \Drupal\block_page\Form\PageVariantAddForm.
 */

namespace Drupal\page_layout\Form;

use Drupal\layout\Plugin\LayoutRegionPluginManager;
use Drupal\page_layout\Form\LayoutRegionFormBase;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for adding a new page variant.
 */
class LayoutRegionAddForm extends LayoutRegionFormBase {

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
   *   The page variant manager.
   */
  public function __construct(LayoutRegionPluginManager $layout_plugin_manager) {
    $this->layoutPluginManager = $layout_plugin_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.layout.region')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'layout_layout_region_add_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function submitText() {
    return $this->t('Add layout region');
  }

  /**
   * {@inheritdoc}
   */
  protected function prepareLayoutRegion($region_id = 'default', $parent_region_id = NULL) {
    $configuration = array(
      'parent' => isset($parent_region_id) ? $parent_region_id : NULL
    );
    $region = $this->layoutPluginManager->createInstance($region_id, $configuration);
    $region_id = $this->pageVariant->addLayoutRegion($region->getConfiguration());
    return $this->pageVariant->getLayoutRegion($region_id);
  }

}
