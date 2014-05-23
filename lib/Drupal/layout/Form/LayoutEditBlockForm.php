<?php

/**
 * @file
 * Contains \Drupal\page_manager\Form\PageVariantEditBlockForm.
 */

namespace Drupal\layout\Form;

use Drupal\layout\Form\LayoutConfigureBlockFormBase;

/**
 * Provides a form for editing a block plugin of a page variant.
 */
class LayoutEditBlockForm extends LayoutConfigureBlockFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'layout_edit_block_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function prepareBlock($block_id) {
    return $this->layout->getBlock($block_id);
  }

  /**
   * {@inheritdoc}
   */
  protected function submitText() {
    return $this->t('Update block');
  }

}
