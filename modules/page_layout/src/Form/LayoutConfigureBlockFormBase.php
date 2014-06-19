<?php

/**
 * @file
 * Contains \Drupal\page_manager\Form\PageVariantConfigureBlockFormBase.
 */

namespace Drupal\page_layout\Form;

use Drupal\page_layout\LayoutStorageInterface;

use Drupal\page_manager\PageInterface;
use Drupal\page_manager\Plugin\ContextAwarePluginAssignmentTrait;
use Drupal\Component\Plugin\ContextAwarePluginInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Url;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CloseDialogCommand;
use Drupal\page_layout\Ajax\LayoutBlockReload;

/**
 * Provides a base form for configuring a block as part of a page variant.
 */
abstract class LayoutConfigureBlockFormBase extends FormBase {

  use ContextAwarePluginAssignmentTrait;

  /**
   * The page entity.
   *
   * @var \Drupal\page_manager\PageInterface;
   */
  protected $page;


  /**
   * The page variant plugin.
   *
   * @var \Drupal\page_manager\Plugin\PageVariantInterface;
   */
  protected $pageVariant;


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
  public function buildForm(array $form, array &$form_state, PageInterface $page = NULL, $page_variant_id = NULL, $layout_region_id = NULL, $block_id = NULL) {

    $this->page = $page;
    $this->pageVariant = $this->page->getVariant($page_variant_id);
    $this->pageVariant->init($page->getExecutable());
    $this->layoutRegion = $this->pageVariant->getLayoutRegion($layout_region_id);

    $this->block = $this->prepareBlock($block_id);

    $form['#tree'] = TRUE;
    $form['settings'] = $this->block->buildConfigurationForm(array(), $form_state);
    $form['settings']['id'] = array(
      '#type' => 'value',
      '#value' => $this->block->getPluginId(),
    );

    // @note: we removed the region widget as it is implicit in URL.
    if ($this->block instanceof ContextAwarePluginInterface) {
      $form['context_assignments'] = $this->addContextAssignmentElement($this->block, $this->pageVariant->getContexts());
    }

    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->submitText(),
      '#button_type' => 'primary',

    );

    if ($this->getRequest()->isXmlHttpRequest()) {
      $form['actions']['submit']['#ajax'] = array(
        'callback' => array($this, 'submitForm')
      );
    }

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

    $this->pageVariant->updateBlock($this->block->getConfiguration()['uuid'], array('region' => $this->layoutRegion->id()));
    $this->page->save();

    if ($this->getRequest()->isXmlHttpRequest()) {
      $response = new AjaxResponse();
      $response->addCommand(new CloseDialogCommand());
      $response->addCommand(new LayoutBlockReload($this->block));

      $form_state['response'] = $response;
      return $response;
    }

    return new Url('page_manager.display_variant_edit', array(
      'page' => $this->page->id(),
      'display_variant_id' => $this->pageVariant->id()
    ));
  }

}
