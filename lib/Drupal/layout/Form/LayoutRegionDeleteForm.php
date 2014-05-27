<?php

/**
 * @file
 * Contains \Drupal\layout\Form\LayoutRegionDeleteForm.
 */

namespace Drupal\layout\Form;

use Drupal\Core\Url;
use Drupal\layout\LayoutStorageInterface;
use Drupal\Core\Form\ConfirmFormBase;


/**
 * Provides a form for deleting a page variant.
 */
class LayoutRegionDeleteForm extends ConfirmFormBase {

  /**
   * The layout this region belongs to.
   *
   * @var \Drupal\layout\LayoutStorageInterface
   */
  protected $layout;

  /**
   * The layout region.
   *
   * @var \Drupal\layout\Plugin\LayoutPluginInterface
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
    return $this->t('Are you sure you want to delete the layout region %region_name from layout %layout_name?', array(
      '%region_name' => $this->layoutRegion->label(),
      '%layout_name' => $this->layout->label()
    ));
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelRoute() {
    return $this->layout->urlInfo('configure-regions-form');
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
  public function buildForm(array $form, array &$form_state, LayoutStorageInterface $layout = NULL, $plugin_id = NULL) {
    $this->layout = $layout;
    $this->layoutRegion = $layout->getLayoutRegion($plugin_id);
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, array &$form_state) {
    $this->layout->removeLayoutRegion($this->layoutRegion->id());
    $this->layout->save();
    drupal_set_message($this->t('The layout region %name has been removed.', array('%name' => $this->layoutRegion->label())));
    $form_state['redirect_route'] = $this->getCancelRoute();
  }

}
