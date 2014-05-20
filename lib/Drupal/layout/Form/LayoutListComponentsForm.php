<?php

/**
 * @file
 * Contains \Drupal\layout\LayoutConfigureComponents.
 */

namespace Drupal\layout\Form;

use \Drupal\Core\Entity\EntityForm;
use \Drupal\Component\Utility\String;
use \Drupal\Component\Serialization\Json;


/**
 * Form controller for node type forms.
 */
class LayoutListComponentsForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, array &$form_state) {
    $form = parent::form($form, $form_state);
    $type = $this->entity;
    $plugins = $this->getAvailableBlocks();
    foreach ($plugins as $plugin_id => $plugin_definition) {
      $category = String::checkPlain($plugin_definition['category']);
      $category_key = 'category-' . $category;
      if (!isset($form['place_blocks']['list'][$category_key])) {
        $form['place_blocks']['list'][$category_key] = array(
          '#type' => 'details',
          '#title' => $category,
          '#open' => TRUE,
          'content' => array(
            '#theme' => 'links',
            '#links' => array(),
            '#attributes' => array(
              'class' => array(
                'block-list',
              ),
            ),
          ),
        );
      }

      $form['place_blocks']['list'][$category_key]['content']['#links'][$plugin_id] = array(
        'title' => $plugin_definition['admin_label'],
        'href' => '/admin/structure/layout/manage/' . $type->id() .'/components/'  . $this->getRequest()->get('layout_region_id') . '/' . $plugin_id . '/add',
        'attributes' => array(
          'class' => array('use-ajax', 'block-filter-text-source'),
          'data-accepts' => 'application/vnd.drupal-modal',
          'data-dialog-options' => Json::encode(array(
              'width' => 700,
            )),
        ),
      );
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, array &$form_state) {
    $actions = array();
    return $actions;
  }

  /**
   * {@inheritdoc}
   */
  public function validate(array $form, array &$form_state) {
    parent::validate($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, array &$form_state) {
    $type = $this->entity;
    $type->id = trim($type->id());
    $type->label= trim($type->label());

    $status = $type->save();

    $t_args = array('%name' => $type->label());

    if ($status == SAVED_UPDATED) {
      drupal_set_message(t('The layout %name has been updated.', $t_args));
    }

    // Not setting a redirect route keeps use on this form.
  }

  public function getAvailableBlocks() {
    $blockManager = \Drupal::service('plugin.manager.block');
    // Sort the plugins first by category, then by label.
    $plugins = $blockManager->getDefinitions();
    uasort($plugins, function ($a, $b) {
      if ($a['category'] != $b['category']) {
        return strnatcasecmp($a['category'], $b['category']);
      }
      return strnatcasecmp($a['admin_label'], $b['admin_label']);
    });

    return $plugins;
  }

}
