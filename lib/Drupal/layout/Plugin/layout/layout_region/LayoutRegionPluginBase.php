<?php

namespace Drupal\layout\Plugin\layout\layout_region;

use Drupal\Component\Plugin\ContextAwarePluginInterface;
use Drupal\layout\Plugin\LayoutPageVariantInterface;
use Drupal\layout\Plugin\LayoutPluginBase;
use Drupal\layout\Plugin\LayoutRegionPluginInterface;


/**
 * The plugin that handles a default region
 *
 * @ingroup layout_region_plugins
 *
 * @LayoutRegion(
 *   id = "default",
 *   title = @Translation("Default"),
 *   help = @Translation("Handles default layout region within a layout."),
 *   contextual_links_locations = {"page"},
 *   theme = "layout_region",
 *   admin = @Translation("Container")
 * )
 */
class LayoutRegionPluginBase extends LayoutPluginBase implements LayoutRegionPluginInterface {
  /**
   *
   * @var \Drupal\layout\Plugin\LayoutPageVariantInterface $pageVariant
   */
  public $pageVariant = NULL;

  public function build(LayoutPageVariantInterface $page_variant, $options = array()) {
    $contexts = $page_variant->getContexts();
    $blocksInRegion = $page_variant->getBlocksByRegion($this->id());
    /** @var $blocksInRegion \Drupal\block\BlockPluginInterface[] */
    $renderArray = array();
    foreach ($blocksInRegion as $id => $block) {
      if ($block instanceof ContextAwarePluginInterface) {
        $page_variant->contextHandler->preparePluginContext($block, $contexts);
      }

      if ($block->access($page_variant->account)) {
        $row = $block->build();
        $block_name = drupal_html_class("block-$id");
        $row['#prefix'] = '<div class="' . $block_name . '">';
        $row['#suffix'] = '</div>';
        $renderArray[] = $row;
      }
    }

    $regions = $this->getSubRegions($page_variant);
    $subregionsRenderArray = array();
    /** @var $renderArray \Drupal\layout\Plugin\LayoutRegionPluginInterface[] */
    if (sizeof($regions)) {
      foreach ($regions as $id => $region) {
        $subregionsRenderArray[] = $region->build($page_variant, $options);
      }
    }

    return array(
      '#theme' => $this->pluginDefinition['theme'],
      '#blocks' => $renderArray,
      '#regions' => $subregionsRenderArray,
      '#region' => $this,
      '#region_id' => $this->id()
    );
  }


  public function getParentRegionId() {
    return isset($this->configuration['parent']) ? $this->configuration['parent'] : NULL;
  }

  public function getParentRegionOptions() {
    $regions = $this->pageVariant->getLayoutRegions();
    $options = array();
    foreach ($regions as $region) {
      // @todo: filter to avoid nesting bugs & filter for valid parent region types.
      if ($region->id() !== $this->id()) {
        $options[$region->id()] = $region->label();
      }
    }
    return $options;
  }

  public function getSubRegions(LayoutPageVariantInterface $page_variant = NULL) {
    // @todo: we need to $this->pageVariant available in a consistent fashion.
    $page_variant = isset($page_variant) ? $page_variant : $this->pageVariant;
    $regions = $page_variant->getLayoutRegions();
    $filtered = array();
    foreach ($regions as $region) {
      if ($region->getParentRegionId() === $this->id()) {
        $filtered[] = $region;
      }
    }
    return $filtered;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, array &$form_state) {
    $form['label'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#description' => $this->t('The label for this region'),
      '#default_value' => $this->label(),
      '#maxlength' => '255',
    );

    $options = $this->getParentRegionOptions();
    $form['parent'] = array(
      '#type' => 'select',
      '#title' => $this->t('Parent region'),
      '#description' => $this->t('Region to nest this region in'),
      '#options' => array(NULL => $this->t('-- No parent region --')) + $this->getParentRegionOptions(),
      '#default_value' => $this->getParentRegionId(),
      '#maxlength' => '255',
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, array &$form_state) {
    $this->configuration['label'] = $form_state['values']['label'];
    $this->configuration['parent'] = isset($form_state['values']['parent']) ?  $form_state['values']['parent'] : NULL;
  }

  public function calculateDependencies() {
    // @todo
  }
}
