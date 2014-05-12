<?php

/**
 * @file
 * Contains \Drupal\block\ComponentFormController.
 */

namespace Drupal\layout;

use Drupal\Core\Cache\Cache;
use Drupal\block\BlockForm;
use Drupal\layout\Entity\LayoutComponent;

/**
 * Provides form controller for component instance forms.
 */
class LayoutContainerFormController extends BlockForm {
  /**
   * {@inheritdoc}
   */
  public function form(array $form, array &$form_state) {
    $entity = $this->entity;

    $id = !$entity->isNew() ? $entity->id() : $this->getLayoutContainerUniqueMachineName($entity);
    // If creating a new block, calculate a safe default machine name.
    $form['id'] = array(
      '#type' => 'machine_name',
      '#maxlength' => 64,
      '#description' => $this->t('A unique name for this container instance. Must be alpha-numeric and underscore separated.'),
      '#default_value' => $id,
      '#machine_name' => array(
        'exists' => 'layout_container_load',
        'replace_pattern' => '[^a-z0-9_.]+',
        'source' => array('settings', 'label'),
      ),
      '#required' => TRUE,
      '#disabled' => !$entity->isNew(),
    );

    $form['layout'] = array(
      '#type' => 'value',
      '#value' => $entity->get('layout'),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, array &$form_state) {
    $actions = parent::actions($form, $form_state);
    $actions['submit']['#value'] = $this->t('Save container');
    return $actions;
  }

  /**
   * {@inheritdoc}
   */
  public function validate(array $form, array &$form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submit(array $form, array &$form_state) {
    $this->entity->save();
    drupal_set_message($this->t('The layout container configuration has been saved.'));
    // Invalidate the content cache and redirect to the block listing,
    // because we need to remove cached block contents for each cache backend.
    Cache::invalidateTags(array('content' => TRUE));
    $form_state['redirect_route'] = array(
      'route_name' => 'layout.configure',
      'route_parameters' => array(
        'layout' => $form_state['block_layout'],
      )
    );
  }

  /**
   * Generates a unique machine name for a block.
   *
   * @param \Drupal\block\BlockInterface $component
   *   The component entity.
   *
   * @return string
   *   Returns the unique name.
   */
  public function getLayoutContainerUniqueMachineName(LayoutComponent $component) {
    $suggestion = $component->getPlugin()->getMachineNameSuggestion();

    // Get all the components which starts with the suggested machine name.
    $query = \Drupal::service('entity.manager')->getStorage('layout_container')->getQuery();
    $query->condition('id', $suggestion, 'CONTAINS');
    $component_ids = $query->execute();

    $component_ids = array_map(function ($id) {
      $parts = explode('.', $id);
      return end($parts);
    }, $component_ids);

    // Iterate through potential IDs until we get a new one. E.g.
    // 'plugin', 'plugin_2', 'plugin_3'...
    $count = 1;
    $machine_default = $suggestion;
    while (in_array($machine_default, $component_ids)) {
      $machine_default = $suggestion . '_' . ++$count;
    }
    return $machine_default;
  }

}
