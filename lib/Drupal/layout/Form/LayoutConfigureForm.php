<?php

/**
 * @file
 * Contains \Drupal\layout\LayoutConfigureComponents.
 */

namespace Drupal\layout\Form;

use Drupal\Core\Entity\EntityForm;
use \Drupal\Component\Utility\String;
use \Drupal\Component\Serialization\Json;


/**
 * Form controller for node type forms.
 */
class LayoutConfigureForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, array &$form_state) {
    $form = parent::form($form, $form_state);

    $type = $this->entity;
    $form['#title'] = $this->t('Configure %label layout', array('%label' => $type->label()));

    $data = array(
      'layout' => array(
        'id' => $type->id(),
        'layoutData' => $type->exportGroupedByContainer(),
        'locked' => $type->isLocked(),
        'webserviceURL' => $this->urlGenerator()->generateFromRoute('layout.rest', array(
            'layout' => $this->entity->id()
          ))
      )
    );


    $form['links'] = array(
      '#type' => 'markup',
      '#markup' => l(t('Preview layout'), $type->get('path'), array('attributes' => array('target' => drupal_html_id($type->id()))))
    );

    // @todo: this is not useful anymore, we use this only as a placeholder for the backbone app.
    $form['components'] = array(
      '#title' => t('Components'),
      '#type' => 'textarea',
      '#default_value' => '',
      '#description' => t('Provide the JSON describing all components of this layout.'),
      '#attached' => array(
        'library' => array(
          'layout/layout'
        ),
        'js' =>  array(
          array('data' =>  is_array($data) ? $data : array(), 'type' => 'setting')
        ),
      ),
    );
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, array &$form_state) {
    return array();
  }

  /**
   * {@inheritdoc}
   */
  public function validate(array $form, array &$form_state) {
    parent::validate($form, $form_state);

    $components = Json::decode($form_state['values']['components']);
    if (!is_array($components)) {
      $this->setFormError('components', $form_state, $this->t("Invalid JSON provided."));
    }
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
