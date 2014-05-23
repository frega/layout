<?php

/**
 * @file
 * Contains \Drupal\page_manager\Form\PageVariantConfigureBlockFormBase.
 */

namespace Drupal\layout\Form;

use Drupal\layout\LayoutStorageInterface;

use Drupal\page_manager\Plugin\ContextAwarePluginAssignmentTrait;
use Drupal\Component\Plugin\ContextAwarePluginInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Url;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CloseDialogCommand;
use Drupal\layout\Ajax\LayoutComponentReload;

/**
 * Provides a base form for configuring a block as part of a page variant.
 */
abstract class LayoutConfigureBlockFormBase extends FormBase {

  use ContextAwarePluginAssignmentTrait;

  /**
   * The page entity.
   *
   * @var \Drupal\layout\LayoutStorageInterface
   */
  protected $layout;

  /**
   * The plugin being configured.
   *
   * @var \Drupal\block\BlockPluginInterface
   */
  protected $block;

  /**
   * Prepares the block plugin based on the block ID.
   *
   * @param string $block_id
   *   Either a block ID, or the plugin ID used to create a new block.
   *
   * @return \Drupal\block\BlockPluginInterface
   *   The block plugin.
   */
  abstract protected function prepareBlock($block_id);

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
  public function buildForm(array $form, array &$form_state, LayoutStorageInterface $layout = NULL, $layout_region_id = NULL, $block_id = NULL) {
    $this->layout = $layout;
    $this->layoutContainer = $this->layout->getLayoutContainer($layout_region_id);

    $this->block = $this->prepareBlock($block_id);

    $form['#tree'] = TRUE;
    $form['settings'] = $this->block->buildConfigurationForm(array(), $form_state);
    $form['settings']['id'] = array(
      '#type' => 'value',
      '#value' => $this->block->getPluginId(),
    );
    $form['region'] = array(
      '#title' => $this->t('Region'),
      '#type' => 'select',
      '#options' => $this->layout->getRegionNames(),
      '#default_value' => $layout_region_id,
      '#required' => TRUE,
    );

    if ($this->block instanceof ContextAwarePluginInterface) {
      $form['context_assignments'] = $this->addContextAssignmentElement($this->block, $this->layout->getContexts());
    }

    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->submitText(),
      '#button_type' => 'primary',
      '#ajax' => array(
        'callback' => array($this, 'submitForm')
      )
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, array &$form_state) {
    $settings = array(
      'values' => &$form_state['values']['settings'],
    );
    // Call the plugin validate handler.
    $this->block->validateConfigurationForm($form, $settings);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, array &$form_state) {
    $settings = array(
      'values' => &$form_state['values']['settings'],
      'errors' => $form_state['errors'],
    );

    // Call the plugin submit handler.
    $this->block->submitConfigurationForm($form, $settings);

    if (!empty($form_state['values']['context_assignments'])) {
      $this->submitContextAssignment($this->block, $form_state['values']['context_assignments']);
    }

    $this->layout->updateBlock($this->block->getConfiguration()['uuid'], array('region' => $form_state['values']['region']));
    $this->layout->save();

    if ($this->getRequest()->isXmlHttpRequest()) {
      $response = new AjaxResponse();
      $response->addCommand(new CloseDialogCommand());
      $response->addCommand(new LayoutComponentReload($this->block));

      $form_state['response'] = $response;
      return $response;
    }

    $form_state['redirect_route'] = new Url('layout.layout_configure', array(
      'layout' => $this->layout->id()
    ));
  }

}
