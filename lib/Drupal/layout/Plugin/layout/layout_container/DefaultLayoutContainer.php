<?php

namespace Drupal\layout\Plugin\layout\layout_container;

use Drupal\layout\Plugin\LayoutContainerBase;


/**
 * The plugin that handles a full page.
 *
 * @ingroup views_display_plugins
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
class DefaultLayoutContainer extends LayoutContainerBase {
  var $configuration = array();

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
