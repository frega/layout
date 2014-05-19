<?php

/**
 * @file
 * Contains \Drupal\block_page\Form\PageVariantFormBase.
 */

namespace Drupal\layout\Form;

use Drupal\layout\LayoutStorageInterface;
use Drupal\Core\Form\FormBase;

/**
 * Provides a base form for editing and adding a page variant.
 */
abstract class LayoutContainerFormBase extends FormBase {

  /**
   * The block page this page variant belongs to.
   *
   * @var \Drupal\layout\LayoutStorageInterface
   */
  protected $layout;

  /**
   * Prepares the page variant used by this form.
   *
   * @param string $container_id
   *   Either a layout container ID, or the plugin ID used to create a new layout container.
   *
   * @return \Drupal\layout\Plugin\layout\layout_container\LayoutContainerPluginBase
   *   The layout container object.
   */
  abstract protected function prepareLayoutContainer($container_id);

  /**
   * Returns the text to use for the submit button.
   *
   * @return string
   *   The submit button text.
   */
  abstract protected function submitText();

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, array &$form_state, LayoutStorageInterface $layout = NULL, $plugin_id = NULL) {

    $this->layout = $layout;
    $this->layoutContainer = $this->prepareLayoutContainer($plugin_id);

    // Allow the page variant to add to the form.
    $form['plugin'] = $this->layoutContainer->buildConfigurationForm(array(), $form_state);
    $form['plugin']['#tree'] = TRUE;

    $form['actions'] = array('#type' => 'actions');
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->submitText(),
      '#button_type' => 'primary',
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, array &$form_state) {
    // Allow the page variant to validate the form.
    $plugin_values = array(
      'values' => &$form_state['values']['plugin'],
    );
    $this->layoutContainer->validateConfigurationForm($form, $plugin_values);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, array &$form_state) {
    // Allow the page variant to submit the form.
    $plugin_values = array(
      'values' => &$form_state['values']['plugin'],
    );
    $this->layoutContainer->submitConfigurationForm($form, $plugin_values);
  }

}
