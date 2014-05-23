<?php

/**
 * @file
 * Contains \Drupal\page_manager\Form\AccessConditionDeleteForm.
 */

namespace Drupal\layout\Form;

use Drupal\layout\LayoutStorageInterface;
use Drupal\Core\Form\ConfirmFormBase;

use Drupal\block\BlockPluginInterface;


/**
 * Provides a form for deleting an access condition.
 */
class LayoutDeleteBlockForm extends ConfirmFormBase {

  /**
   * The page entity this selection condition belongs to.
   *
   * @var \Drupal\layout\LayoutStorageInterface
   */
  protected $layout;

  /**
   * The access condition used by this form.
   *
   * @var \Drupal\block\BlockPluginInterface
   */
  protected $block;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'layout_block_delete_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    $label = $this->block->getConfiguration()['label'];
    return t('Are you sure you want to delete the block @name?', array('@name' => $label));
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelRoute() {
    return $this->layout->urlInfo('configure-form');
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
  public function buildForm(array $form, array &$form_state, LayoutStorageInterface $layout = NULL, $layout_region_id = NULL, $block_id = NULL) {
    $this->layout = $layout;
    $this->block = $this->layout->getBlock($block_id);
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, array &$form_state) {
    $this->layout->removeBlock($this->block->getConfiguration()['uuid']);
    $this->layout->save();
    drupal_set_message($this->t('The block %name has been removed.', array('%name' => $this->block->getConfiguration()['label'])));
    $form_state['redirect_route'] = $this->getCancelRoute();
  }

}
