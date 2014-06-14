<?php

/**
 * @file
 * Contains \Drupal\page_manager\Form\AccessConditionDeleteForm.
 */

namespace Drupal\page_layout\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CloseDialogCommand;
use Drupal\Core\Form\ConfirmFormBase;

use Drupal\Core\Url;
use Drupal\page_layout\Ajax\LayoutBlockDelete;
use Drupal\page_manager\PageInterface;
use \Drupal\page_manager\Plugin\PageVariantInterface;

/**
 * Provides a form for deleting an access condition.
 */
class LayoutDeleteBlockForm extends ConfirmFormBase {

  /**
   * The page entity this selection condition belongs to.
   *
   * @var \Drupal\page_manager\PageInterface;
   */
  protected $page;

  /**
   * The page variant plugin.
   *
   * @var \Drupal\page_manager\Plugin\PageVariantInterface
   */
  protected $pageVariant;

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
  public function buildForm(array $form, array &$form_state, PageInterface $page = NULL, $page_variant_id = NULL, $layout_region_id = NULL, $block_id = NULL) {
    $this->page = $page;
    $this->pageVariant = $this->page->getVariant($page_variant_id);
    $this->block = $this->pageVariant->getBlock($block_id);
    $form = parent::buildForm($form, $form_state, $page, $page_variant_id, $layout_region_id, $block_id);

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
  public function submitForm(array &$form, array &$form_state) {
    $this->pageVariant->removeBlock($this->block->getConfiguration()['uuid']);
    $this->page->save();
    drupal_set_message($this->t('The block %name has been removed.', array('%name' => $this->block->getConfiguration()['label'])));

    if ($this->getRequest()->isXmlHttpRequest()) {
      $response = new AjaxResponse();
      $response->addCommand(new CloseDialogCommand());
      $response->addCommand(new LayoutBlockDelete($this->block));
      $form_state['response'] = $response;
      return $response;
    }

    $form_state['redirect_route'] = $this->getCancelRoute();
  }

}
