<?php

namespace Drupal\layout\Plugin\layout\layout_region;

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
  public function build(LayoutPageVariantInterface $page_variant, $options = array()) {
    $blocksInRegion = $page_variant->getBlocksByRegion($this->id());
    $regionRenderArray = array();
    foreach ($blocksInRegion as $id => $block) {
      $regionRenderArray[] = $block->build();
    }

    return array(
      '#theme' => $this->pluginDefinition['theme'],
      '#blocks' => $regionRenderArray,
      '#region_id' => $this->id()
    );
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
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, array &$form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, array &$form_state) {
    $this->configuration['label'] = $form_state['values']['label'];
  }

  public function calculateDependencies() {
    // @todo
  }
}
