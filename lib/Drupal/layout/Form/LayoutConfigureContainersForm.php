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
class LayoutConfigureContainersForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, array &$form_state) {
    $form = parent::form($form, $form_state);

    $type = $this->entity;
    $form['#title'] = $this->t('Configure containers for %label layout', array('%label' => $type->label()));

    $form['template'] = array(
      '#title' => t('Switch template'),
      '#description' => t('Warning: currently this just deletes and does *not* reassign components!'),
      '#type' => 'select',
      '#default_value' => $this->entity->getLayoutTemplateId(),
      '#options' => $this->entity->getLayoutTemplateOptions(),
    );

    $layoutContainerManager = \Drupal::service('plugin.manager.layout.layout_container');
    // Sort the plugins first by category, then by label.
    $plugins = $layoutContainerManager->getDefinitions();
    $operations = array();
    foreach ($plugins as $plugin) {
      $operations['layout_container_add_' .  $plugin['id']] = array(
        'title' => $this->t('Add @name container', array('@name' => $plugin['title'])),
        'route_name' => 'layout.layout_container_add',
        'route_parameters' => array(
          'layout' => $this->entity->id(),
          'plugin_id' => $plugin['id']
        ),
      );
    }

    $form['add_container'] = array(
      '#type' => 'operations',
      '#links' => $operations,
    );

    $form['layout_containers']['table'] = array(
      '#type' => 'table',
      '#header' => array(
        $this->t('Label'),
        $this->t('Plugin ID'),
        $this->t('Weight'),
        $this->t('Operations'),
      ),
      '#empty' => $this->t('There are no layout containers.'),
      '#tabledrag' => array(array(
        'action' => 'order',
        'relationship' => 'sibling',
        'group' => 'page-variant-weight',
      )),
    );

    foreach ($this->entity->getLayoutContainers() as $container_id => $container) {
      $row = array(
        '#attributes' => array(
          'class' => array('draggable'),
        ),
      );
      $row['label']['#markup'] = $container->label();
      $row['id']['#markup'] = $container->getPluginId();
      $row['weight'] = array(
        '#type' => 'weight',
        '#default_value' => $container->getWeight(),
        '#title' => t('Weight for @container page variant', array('@container' => $container->label())),
        '#title_display' => 'invisible',
        '#attributes' => array(
          'class' => array('page-variant-weight'),
        ),
      );
      $operations = array();
      $operations['edit'] = array(
        'title' => $this->t('Edit'),
        'route_name' => 'layout.layout_container_edit',
        'route_parameters' => array(
          'layout' => $this->entity->id(),
          'plugin_id' => $container_id,
        ),
      );

      $operations['delete'] = array(
        'title' => $this->t('Delete'),
        'route_name' => 'layout.layout_container_delete',
        'route_parameters' => array(
          'layout' => $this->entity->id(),
          'plugin_id' => $container_id,
        ),
      );

      $row['operations'] = array(
        '#type' => 'operations',
        '#links' => $operations,
      );
      $form['layout_containers']['table'][$container_id] = $row;
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, array &$form_state) {
    $actions = parent::actions($form, $form_state);
    $actions['submit']['#value'] = t('Save layout');
    $actions['delete']['#access'] = FALSE;
    return $actions;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, array &$form_state) {
    if (!empty($form_state['values']['table'])) {
      foreach ($form_state['values']['table'] as $container_id => $data) {
        if ($container = $this->entity->getLayoutContainer($container_id)) {
          $container->setWeight($data['weight']);
        }
      }
    }

    $type = $this->entity;
    $status = $type->save();

    $t_args = array('%name' => $type->label());

    if ($status == SAVED_UPDATED) {
      drupal_set_message(t('The layout %name has been updated.', $t_args));
    }
    elseif ($status == SAVED_NEW) {
      drupal_set_message(t('The layout %name has been added.', $t_args));
    }
  }

}
