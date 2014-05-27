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
class LayoutConfigureRegionsForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, array &$form_state) {
    $form = parent::form($form, $form_state);

    $type = $this->entity;
    $form['#title'] = $this->t('Configure the layout/template for %label layout', array('%label' => $type->label()));

    $form['template'] = array(
      '#title' => t('Switch template'),
      '#description' => t('Warning: currently this just deletes and does *not* reassign blocks!'),
      '#type' => 'select',
      '#default_value' => $this->entity->getLayoutTemplateId(),
      '#options' => $this->entity->getLayoutTemplateOptions(),
    );

    $layoutRegionManager = \Drupal::service('plugin.manager.layout.layout_region');
    // Sort the plugins first by category, then by label.
    $plugins = $layoutRegionManager->getDefinitions();
    $operations = array();
    foreach ($plugins as $plugin) {
      $operations['layout_region_add_' .  $plugin['id']] = array(
        'title' => $this->t('Add @name region', array('@name' => $plugin['title'])),
        'route_name' => 'layout.layout_region_add',
        'route_parameters' => array(
          'layout' => $this->entity->id(),
          'plugin_id' => $plugin['id']
        ),
      );
    }

    $form['add_region'] = array(
      '#prefix' => t('Note: you can add more region/regions to this template'),
      '#type' => 'operations',
      '#links' => $operations,
    );

    $form['layout_regions']['table'] = array(
      '#type' => 'table',
      '#header' => array(
        $this->t('Label'),
        $this->t('Plugin ID'),
        $this->t('Weight'),
        $this->t('Operations'),
      ),
      '#empty' => $this->t('There are no layout regions.'),
      '#tabledrag' => array(array(
        'action' => 'order',
        'relationship' => 'sibling',
        'group' => 'page-variant-weight',
      )),
    );

    foreach ($this->entity->getLayoutRegions() as $region_id => $region) {
      $row = array(
        '#attributes' => array(
          'class' => array('draggable'),
        ),
      );
      $row['label']['#markup'] = $region->label();
      $row['id']['#markup'] = $region->getPluginId();
      $row['weight'] = array(
        '#type' => 'weight',
        '#default_value' => $region->getWeight(),
        '#title' => t('Weight for @region page variant', array('@region' => $region->label())),
        '#title_display' => 'invisible',
        '#attributes' => array(
          'class' => array('page-variant-weight'),
        ),
      );
      $operations = array();
      $operations['edit'] = array(
        'title' => $this->t('Edit'),
        'route_name' => 'layout.layout_region_edit',
        'route_parameters' => array(
          'layout' => $this->entity->id(),
          'plugin_id' => $region_id,
        ),
      );

      $operations['delete'] = array(
        'title' => $this->t('Delete'),
        'route_name' => 'layout.layout_region_delete',
        'route_parameters' => array(
          'layout' => $this->entity->id(),
          'plugin_id' => $region_id,
        ),
      );

      $row['operations'] = array(
        '#type' => 'operations',
        '#links' => $operations,
      );
      $form['layout_regions']['table'][$region_id] = $row;
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
      foreach ($form_state['values']['table'] as $region_id => $data) {
        if ($region = $this->entity->getLayoutRegion($region_id)) {
          $region->setWeight($data['weight']);
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
