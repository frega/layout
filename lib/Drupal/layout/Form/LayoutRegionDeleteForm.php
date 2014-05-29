<?php

/**
 * @file
 * Contains \Drupal\layout\Form\LayoutRegionDeleteForm.
 */

namespace Drupal\layout\Form;

use Drupal\page_manager\PageInterface;
use Drupal\Core\Form\ConfirmFormBase;
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
   * @var \Drupal\layout\Plugin\LayoutPageVariantInterface
   */
  protected $pageVariant;

  /**
   * The layout region.
   *
   * @var \Drupal\layout\Plugin\LayoutRegionPluginInterface
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
  public function getCancelRoute() {
    return new Url('page_manager.page_variant_edit', array(
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
  public function buildForm(array $form, array &$form_state, PageInterface $page = NULL, $page_variant_id = NULL,  $layout_region_id = NULL) {
    $this->page = $page;
    $this->pageVariant = $page->getPageVariant($page_variant_id);
    $this->layoutRegion = $this->pageVariant->getLayoutRegion($layout_region_id);

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, array &$form_state) {
    $this->pageVariant->removeLayoutRegion($this->layoutRegion->id());
    $this->page->save();
    drupal_set_message($this->t('The layout region %name has been removed.', array('%name' => $this->layoutRegion->label())));
    $form_state['redirect_route'] = $this->getCancelRoute();
  }

}
