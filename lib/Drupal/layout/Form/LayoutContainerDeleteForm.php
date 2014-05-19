<?php

/**
 * @file
 * Contains \Drupal\layout\Form\LayoutContainerDeleteForm.
 */

namespace Drupal\layout\Form;

use Drupal\layout\LayoutStorageInterface;
use Drupal\Core\Form\ConfirmFormBase;

/**
 * Provides a form for deleting a page variant.
 */
class LayoutContainerDeleteForm extends ConfirmFormBase {

  /**
   * The layout this container belongs to.
   *
   * @var \Drupal\layout\LayoutStorageInterface
   */
  protected $layout;

  /**
   * The layout container.
   *
   * @var \Drupal\layout\Plugin\LayoutContainerInterface
   */
  protected $layoutContainer;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'layout_layout_container_delete_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete the layout container %container_name from layout %layout_name?', array(
      '%container_name' => $this->layoutContainer->label(),
      '%layout_name' => $this->layout->label()
    ));
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelRoute() {
    return $this->layout->urlInfo('configure-containers-form');
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
  public function buildForm(array $form, array &$form_state, LayoutStorageInterface $layout = NULL, $layout_container = NULL) {
    $this->layout = $layout;
    $this->layoutContainer = $layout->getLayoutContainer($layout_container);
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, array &$form_state) {
    $this->layout->removeLayoutContainer($this->layoutContainer->id());
    $this->layout->save();
    drupal_set_message($this->t('The layout container %name has been removed.', array('%name' => $this->layoutContainer->label())));
    $form_state['redirect_route'] = $this->getCancelRoute();
  }

}
