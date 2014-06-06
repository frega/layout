<?php

/**
 * @file
 * Contains \Drupal\block_page\Form\PageVariantFormBase.
 */

namespace Drupal\page_layout\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CloseDialogCommand;
use Drupal\Core\Url;
use Drupal\page_layout\Ajax\LayoutRegionReload;
use Drupal\page_layout\Ajax\LayoutReload;
use Drupal\page_manager\PageInterface;
use Drupal\Core\Form\FormBase;

/**
 * Provides a base form for editing and adding a page variant.
 */
abstract class LayoutRegionFormBase extends FormBase {

  /**
   * The block page this page variant belongs to.
   *
   * @var \Drupal\page_manager\PageInterface
   */
  protected $page;

  /**
   * The block page this page variant belongs to.
   *
   * @var \Drupal\page_layout\Plugin\LayoutPageVariantInterface
   */
  protected $pageVariant;


  /**
   * The block page this page variant belongs to.
   *
   * @var \Drupal\page_layout\Plugin\LayoutRegionPluginInterface
   */
  protected $layoutRegion;

  /**
   * Prepares the page variant used by this form.
   *
   * @param string $region_id
   *   Either a layout region ID, or the plugin ID used to create a new layout region.
   *
   * @return \Drupal\page_layout\Plugin\layout\layout_region\LayoutRegionPluginBase
   *   The layout region object.
   */
  abstract protected function prepareLayoutRegion($region_id);

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
  public function buildForm(array $form, array &$form_state, PageInterface $page = NULL, $page_variant_id = NULL,  $layout_region_id = NULL, $plugin_id = NULL) {
    $this->page = $page;
    $this->pageVariant = $page->getPageVariant($page_variant_id);

    // Check for adding a (sub-)region to an existent region.
    if ($plugin_id) {
      $this->layoutRegion = $this->prepareLayoutRegion($plugin_id, $layout_region_id);
      $this->layoutRegion->parentLayoutRegion = $this->pageVariant->getLayoutRegion($layout_region_id);
    }
    else {
      $this->layoutRegion = $this->prepareLayoutRegion($layout_region_id);
    }


    // Allow the page variant to add to the form.
    $form['plugin'] = $this->layoutRegion->buildConfigurationForm(array(), $form_state);
    $form['plugin']['#tree'] = TRUE;

    $form['actions'] = array('#type' => 'actions');
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
    // Allow the page variant to validate the form.
    $plugin_values = array(
      'values' => &$form_state['values']['plugin'],
    );
    $this->layoutRegion->validateConfigurationForm($form, $plugin_values);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, array &$form_state) {
    // Allow the page variant to submit the form.
    $plugin_values = array(
      'values' => &$form_state['values']['plugin'],
    );
    $this->layoutRegion->submitConfigurationForm($form, $plugin_values);

    $this->pageVariant->updateLayoutRegion($this->layoutRegion->id(), array(
      'label' => $this->layoutRegion->label(),
      'parent' => $this->layoutRegion->getParentRegionId()
    ));

    $this->page->save();

    if ($this->getRequest()->isXmlHttpRequest()) {
      $response = new AjaxResponse();
      $response->addCommand(new CloseDialogCommand());
      if ($this->getFormId() === 'layout_layout_region_add_form') {
        $response->addCommand(new LayoutReload($this->page, $this->pageVariant));
      } else {
        $response->addCommand(new LayoutRegionReload($this->pageVariant, $this->layoutRegion));
      }

      $form_state['response'] = $response;
      return $response;
    }

    $form_state['redirect_route'] = new Url('page_manager.page_variant_edit', array(
      'page' => $this->page->id(),
      'page_variant_id' => $this->pageVariant->id()
    ));
  }

}
