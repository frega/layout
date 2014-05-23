<?php

/**
 * @file
 * Contains \Drupal\page_manager\Form\PageVariantAddBlockForm.
 */

namespace Drupal\layout\Form;

use Drupal\Component\Plugin\PluginManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Drupal\layout\Form\LayoutConfigureBlockFormBase;


/**
 * Provides a form for adding a block plugin to a page variant.
 */
class LayoutAddBlockForm extends LayoutConfigureBlockFormBase {

  /**
   * The block manager.
   *
   * @var \Drupal\Component\Plugin\PluginManagerInterface
   */
  protected $blockManager;

  /**
   * Constructs a new PageVariantFormBase.
   */
  public function __construct(PluginManagerInterface $block_manager) {
    $this->blockManager = $block_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.block')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'layout_add_block_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function prepareBlock($plugin_id) {
    $block = $this->blockManager->createInstance($plugin_id);
    $block_id = $this->layout->addBlock($block->getConfiguration());
    return $this->layout->getBlock($block_id);
  }

  /**
   * {@inheritdoc}
   */
  protected function submitText() {
    return $this->t('Add block');
  }

}
