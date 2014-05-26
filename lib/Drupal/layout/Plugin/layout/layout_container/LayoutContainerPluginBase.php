<?php

namespace Drupal\layout\Plugin\layout\layout_container;

use Drupal\layout\Plugin\LayoutPageVariantInterface;
use Drupal\layout\Plugin\LayoutPluginBase;
use Drupal\layout\Plugin\LayoutContainerPluginInterface;


/**
 * The plugin that handles a default container
 *
 * @ingroup layout_container_plugins
 *
 * @LayoutContainer(
 *   id = "default",
 *   title = @Translation("Default"),
 *   help = @Translation("Handles default layout container within a layout."),
 *   contextual_links_locations = {"page"},
 *   theme = "layout_container",
 *   admin = @Translation("Container")
 * )
 */
class LayoutContainerPluginBase extends LayoutPluginBase implements LayoutContainerPluginInterface {
  public function build(LayoutPageVariantInterface $page_variant, $options = array()) {
    $blocksInRegion = $page_variant->getBlocksByRegion($this->id());
    $regionRenderArray = array();
    foreach ($blocksInRegion as $id => $component) {
      $regionRenderArray[] = $component->build();
    }

    return array(
      '#theme' => $this->pluginDefinition['theme'],
      '#components' => $regionRenderArray,
      '#container_id' => $this->id()
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, array &$form_state) {
    $form['label'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#description' => $this->t('The label for this container'),
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
