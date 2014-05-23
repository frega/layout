<?php

namespace Drupal\layout\Plugin\layout\layout_template;

use Drupal\layout\LayoutStorageInterface;
use Drupal\layout\Plugin\LayoutContainerPluginBag;
use Drupal\layout\Plugin\LayoutPluginBase;
use Drupal\layout\Plugin\LayoutTemplatePluginInterface;

/**
 * The plugin that handles the default layout template.
 *
 * @ingroup layout_template_plugins
 *
 * @LayoutTemplate(
 *   id = "default",
 *   title = @Translation("Default template"),
 *   help = @Translation("Default layout template."),
 *   contextual_links_locations = {"page"},
 *   theme = "layout_template",
 *   admin = @Translation("Layout template"),
 *   derivative = "\Drupal\layout\Plugin\layout\layout_template\Derivative\DerivativeLayoutTemplate"
 * )
 */
class LayoutTemplatePluginBase extends LayoutPluginBase implements LayoutTemplatePluginInterface {
  var $configuration = array();

  public function getLayoutContainerPluginDefinitions() {
    return isset($this->pluginDefinition['containers']) ? $this->pluginDefinition['containers'] : array();
  }

  public function build(LayoutStorageInterface $layout, $options = array()) {
    $containers = $layout->getLayoutContainers();
    $renderArray = array();
    foreach ($containers as $container) {
      $renderArray[] = array(
        '#theme' => 'layout_container',
        '#components' => $container->build($layout, $options),
        '#container_id' => $container->id()
      );
    }

    return array(
      '#theme' => 'layout',
      '#containers' => $renderArray
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, array &$form_state) {
    $form['containers'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Containers'),
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
    $this->configuration['containers'] = $form_state['values']['containers'];
  }

  public function calculateDependencies() {
    // @todo
  }
}
