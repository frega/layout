<?php

/**
 * @file
 * Contains \Drupal\layout\NodeTypeFormController.
 */

namespace Drupal\layout;

use Drupal\Core\Entity\EntityFormController;
use Drupal\Component\Utility\String;

/**
 * Form controller for node type forms.
 */
class LayoutFormController extends EntityFormController {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, array &$form_state) {
    $form = parent::form($form, $form_state);

    $type = $this->entity;
    if ($this->operation == 'add') {
      $form['#title'] = String::checkPlain($this->t('Add layout'));
    }
    elseif ($this->operation == 'edit') {
      $form['#title'] = $this->t('Edit %label layout', array('%label' => $type->label()));
    }

    $form['label'] = array(
      '#title' => t('Label'),
      '#type' => 'textfield',
      '#default_value' => $type->label(),
      '#description' => t('The human-readable name of this content type. This text will be displayed as part of the list on the <em>Layouts</em> page. It is recommended that this name begin with a capital letter and contain only letters, numbers, and spaces. This name must be unique.'),
      '#required' => TRUE,
      '#size' => 30,
    );

    $form['id'] = array(
      '#type' => 'machine_name',
      '#default_value' => $type->id(),
      '#maxlength' => 32,
      '#disabled' => $type->isLocked(),
      '#machine_name' => array(
        'exists' => 'layout_load',
        'source' => array('label'),
      ),
      '#description' => t('A unique machine-readable name for this layout. It must only contain lowercase letters, numbers, and underscores.')
    );

    $form['path'] = array(
      '#title' => t('Path'),
      '#required' => TRUE,
      '#type' => 'textfield',
      '#default_value' => $type->get('path'),
      '#maxlength' => 255,
      '#disabled' => $type->isLocked(),
      '#description' => t('A unique path this page layout.')
    );

    $form['containers'] = array(
      '#title' => t('Containers'),
      '#required' => TRUE,
      '#type' => 'textarea',
      '#default_value' => $type->get('containers') ?  $type->get('containers') : json_encode(array(array('id' => 'first', 'label' => 'First'), array('id' => 'second', 'label' => 'Second'))),
      '#disabled' => $type->isLocked(),
      '#description' => t('Enter JSON representing the containers ("regions")')
    );

    $form['description'] = array(
      '#title' => t('Description'),
      '#type' => 'textarea',
      '#default_value' => $type->description,
      '#description' => t('Describe this layout.'),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, array &$form_state) {
    $actions = parent::actions($form, $form_state);
    $actions['submit']['#value'] = t('Save layout');
    $actions['delete']['#value'] = t('Delete layout');
    $actions['delete']['#access'] = $this->entity->access('delete');
    return $actions;
  }

  /**
   * {@inheritdoc}
   */
  public function validate(array $form, array &$form_state) {
    parent::validate($form, $form_state);

    $containers = trim($form_state['values']['containers']);
    if (!$containers || !json_decode($containers)) {
      $this->setFormError('containers', $form_state, $this->t("Invalid JSON provided"));
    }

    // @todo: validate path for uniqueness etc.
    $id = trim($form_state['values']['id']);
    // '0' is invalid, since elsewhere we check it using empty().
    if ($id == '0') {
      $this->setFormError('id', $form_state, $this->t("Invalid machine-readable name. Enter a name other than %invalid.", array('%invalid' => $id)));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, array &$form_state) {
    $type = $this->entity;
    $type->id = trim($type->id());
    $type->label= trim($type->label());
    $type->path = trim($type->get('path'));

    $status = $type->save();

    $t_args = array('%name' => $type->label());

    if ($status == SAVED_UPDATED) {
      drupal_set_message(t('The layout %name has been updated.', $t_args));
    }
    elseif ($status == SAVED_NEW) {
      drupal_set_message(t('The layout %name has been added.', $t_args));
      watchdog('layout', 'Added layout %name.', $t_args, WATCHDOG_NOTICE, l(t('view'), 'admin/structure/layouts'));
    }

    $form_state['redirect_route']['route_name'] = 'layout.overview';

    // @todo: clear route cache on change of path property ...

    $controller = $form_state['controller'];
  }

}
