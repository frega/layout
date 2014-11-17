<?php

/**
 * @file
 * Contains \Drupal\page_layout\Form\LayoutRegionDeleteForm.
 */

namespace Drupal\page_layout\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CloseDialogCommand;
use Drupal\page_layout\Ajax\LayoutReload;
use Drupal\page_manager\PageInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;


/**
 * Provides a form for deleting a page variant.
 */
class LayoutRegionDeleteForm extends ConfirmFormBase {

  /**
   * The layout this region belongs to.
   *
   * @var \Drupal\page_manager\PageInterface
   */
  protected $page;

  /**
   * The layout this region belongs to.
   *
   * @var \Drupal\page_layout\Plugin\LayoutPageVariantInterface
   */
  protected $pageVariant;

  /**
   * The layout region.
   *
   * @var \Drupal\page_layout\Plugin\LayoutRegionPluginInterface
   */
  protected $layoutRegion;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'layout_layout_region_delete_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete the layout region %region_name from %name?', array(
      '%region_name' => $this->layoutRegion->label(),
      '%name' => $this->pageVariant->label()
    ));
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('page_manager.display_variant_edit', array(
      'page' => $this->page->id(),
      'page_variant_id' => $this->pageVariant->id()
    ));
  }
  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, PageInterface $page = NULL, $page_variant_id = NULL,  $layout_region_id = NULL) {
    $this->page = $page;
    $this->pageVariant = $page->getVariant($page_variant_id);
    $this->pageVariant->init($this->page->getExecutable());
    $this->layoutRegion = $this->pageVariant->getLayoutRegion($layout_region_id);

    $form = parent::buildForm($form, $form_state);

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
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->pageVariant->removeLayoutRegion($this->layoutRegion->id());
    $this->page->save();
    drupal_set_message($this->t('The layout region %name has been removed.', array('%name' => $this->layoutRegion->label())));

    if ($this->getRequest()->isXmlHttpRequest()) {
      $response = new AjaxResponse();
      $response->addCommand(new CloseDialogCommand());
      $response->addCommand(new LayoutReload($this->pageVariant));
      $form_state->setResponse($response);
      return $response;
    }

    $form_state->setRedirect($this->getCancelRoute());
  }

}
