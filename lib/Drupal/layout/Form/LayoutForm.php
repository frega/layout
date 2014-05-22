<?php

/**
 * @file
 * Contains \Drupal\layout\NodeTypeFormController.
 */

namespace Drupal\layout\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Component\Utility\String;

/**
 * Form controller for node type forms.
 */
class LayoutForm extends EntityForm {

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

    // A new layout needs a template.
    if ($this->operation == 'add') {
      $form['template'] = array(
        '#title' => t('Layout template'),
        '#type' => 'select',
        '#default_value' => $this->entity->getLayoutTemplateId(),
        '#options' => $this->entity->getLayoutTemplateOptions(),
        '#required' => TRUE
      );
    }

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
    // @todo: validate path for uniqueness etc.

    // @note: this should probably be removed, just copied from elsewhere ...
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

      $form_state['redirect_route'] = array(
        'route_name' => 'layout.layout_edit',
        'route_parameters' => array(
          'layout' => $type->id(),
        ),
      );
    }
  }

}
