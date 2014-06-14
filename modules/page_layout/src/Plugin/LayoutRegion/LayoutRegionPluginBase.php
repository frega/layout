<?php

namespace Drupal\page_layout\Plugin\LayoutRegion;

use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Component\Plugin\ContextAwarePluginInterface;
use Drupal\page_layout\Plugin\LayoutPageVariantInterface;
use Drupal\layout\Plugin\LayoutRegion\LayoutConfigurableRegionBase;
use Drupal\layout\Plugin\LayoutRegion\LayoutConfigurableRegionInterface;


/**
 * The plugin that handles a default region
 *
 * @ingroup layout_region_plugins
 *
 * @LayoutRegion(
 *   id = "default",
 *   label = @Translation("Default region"),
 *   help = @Translation("Handles default layout region within a layout."),
 *   contextual_links_locations = {"page"},
 *   theme = "layout_region",
 *   admin = @Translation("Container")
 * )
 */
class LayoutRegionPluginBase extends LayoutConfigurableRegionBase {
  /**
   *
   * @var \Drupal\page_layout\Plugin\LayoutPageVariantInterface $pageVariant
   */
  public $pageVariant = NULL;

  public function build(LayoutPageVariantInterface $page_variant, $options = array()) {
    $contexts = $page_variant->getContexts();
    $blocksInRegion = $page_variant->getBlocksByRegion($this->id());
    /** @var $blocksInRegion \Drupal\block\BlockPluginInterface[] */
    $renderArray = array();
    foreach ($blocksInRegion as $id => $block) {
      if ($block instanceof ContextAwarePluginInterface) {
        $mapping = array();
        if ($block instanceof ConfigurablePluginInterface) {
          $configuration = $block->getConfiguration();
          if (isset($configuration['context_mapping'])) {
            $mapping = array_flip($configuration['context_mapping']);
          }
        }
        $page_variant->getContextHandler()->applyContextMapping($block, $contexts, $mapping);
      }

      if ($block->access($page_variant->account)) {
        $block_render_array = $block->build();
        $block_name = drupal_html_class("block-$id");
        $block_render_array['#prefix'] = '<div class="' . $block_name . '">';
        $block_render_array['#suffix'] = '</div>';

        $renderArray[] = $block_render_array;
      }
    }

    $regions = $this->getSubRegions($page_variant);
    $subregionsRenderArray = array();
    /** @var $renderArray \Drupal\page_layout\Plugin\LayoutRegionPluginInterface[] */
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
    $contained_region_ids = $this->getAllContainedRegionIds();
    foreach ($regions as $region) {
      // @todo: filter to avoid nesting bugs & filter for valid parent region types.
      if ($region->id() !== $this->id() && !in_array($region->id(), $contained_region_ids)) {
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

  public function getAllContainedRegionIds(LayoutPageVariantInterface $page_variant = NULL) {
    $page_variant = isset($page_variant) ? $page_variant : $this->pageVariant;
    $regions = $this->getSubRegions($page_variant);
    $contained = array();
    if (sizeof($regions)) {
      foreach ($regions as $region) {
        $contained = array_merge($contained, array($region->id()), $region->getAllContainedRegionIds($page_variant));
      }
    }
    return $contained;
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

    $form['region_positioning'] = array(
      '#type' => 'details',
      '#title' => t('Region positioning (parent and weight)'),
      '#open' => FALSE
    );

    $options = $this->getParentRegionOptions();
    $form['region_positioning']['parent'] = array(
      '#type' => 'select',
      '#title' => $this->t('Parent region'),
      '#description' => $this->t('Region to nest this region in'),
      '#options' => array(NULL => $this->t('-- No parent region --')) + $this->getParentRegionOptions(),
      '#default_value' => $this->getParentRegionId(),
      '#maxlength' => '255',
    );

    $form['region_positioning']['weight'] = array(
      '#type' => 'weight',
      '#title' => $this->t('Weight'),
      '#description' => $this->t('Weight of this region'),
      '#default_value' => $this->getWeight(),
      '#maxlength' => '255',
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, array &$form_state) {
    $this->configuration['label'] = $form_state['values']['label'];
    $this->configuration['parent'] = isset($form_state['values']['region_positioning']['parent']) ?  $form_state['values']['region_positioning']['parent'] : NULL;
    $this->configuration['weight'] = isset($form_state['values']['region_positioning']['weight']) ?  $form_state['values']['region_positioning']['weight'] : 0;
  }

  public function calculateDependencies() {
    // @todo
  }
}
