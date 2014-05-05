<?php

/**
 * @file
 * Contains \Drupal\block\ComponentFormController.
 */

namespace Drupal\layout;

use Drupal\Core\Cache\Cache;
use Drupal\block\BlockFormController;
use Drupal\layout\Entity\LayoutComponent;


use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CloseDialogCommand;
use Drupal\layout\Ajax\LayoutComponentReload;


/**
 * Provides form controller for component instance forms.
 */
class LayoutComponentFormController extends BlockFormController {
  /**
   * {@inheritdoc}
   */
  public function form(array $form, array &$form_state) {
    $entity = $this->entity;

    $form['#tree'] = TRUE;
    $form['settings'] = $entity->getPlugin()->buildConfigurationForm(array(), $form_state);

    $id = !$entity->isNew() ? $entity->id() : $this->getLayoutComponentUniqueMachineName($entity);
    // If creating a new block, calculate a safe default machine name.
    $form['id'] = array(
      '#type' => 'machine_name',
      '#maxlength' => 64,
      '#description' => $this->t('A unique name for this block instance. Must be alpha-numeric and underscore separated.'),
      '#default_value' => $id,
      '#machine_name' => array(
        'exists' => 'layout_component_load',
        'replace_pattern' => '[^a-z0-9_.]+',
        'source' => array('settings', 'label'),
      ),
      '#required' => TRUE,
      '#disabled' => !$entity->isNew(),
    );

    $form_state['layout'] = $entity->get('layout');

    $form['#attached']['css'] = array(
      drupal_get_path('module', 'block') . '/css/block.admin.css',
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, array &$form_state) {
    $actions = parent::actions($form, $form_state);
    $actions['submit']['#value'] = $this->t('Save component');
    if ($this->getRequest()->isXmlHttpRequest()) {
      $actions['submit']['#ajax'] = array(
        'callback' => array($this, 'submit')
      );
    }
    unset($actions['delete']);
    return $actions;
  }

  /**
   * {@inheritdoc}
   */
  public function validate(array $form, array &$form_state) {
    $this->updateFormLangcode($form_state);

    // The Block Entity form puts all block plugin form elements in the
    // settings form element, so just pass that to the block for validation.
    $settings = array(
      'values' => &$form_state['values']['settings']
    );
    // Call the plugin validate handler.
    $this->entity->getPlugin()->validateConfigurationForm($form, $settings);
  }

  /**
   * {@inheritdoc}
   */
  public function submit(array $form, array &$form_state) {
    // Remove button and internal Form API values from submitted values.
    form_state_values_clean($form_state);

    $this->entity = $this->buildEntity($form, $form_state);

    $entity = $this->entity;
    // The Block Entity form puts all block plugin form elements in the
    // settings form element, so just pass that to the block for submission.
    // @todo Find a way to avoid this manipulation.
    $settings = array(
      'values' => &$form_state['values']['settings'],
      'errors' => $form_state['errors'],
    );

    // Call the plugin submit handler.
    $entity->getPlugin()->submitConfigurationForm($form, $settings);

    // Save the settings of the plugin.
    $entity->save();

    drupal_set_message($this->t('The layout component configuration has been saved.'));
    // Invalidate the content cache and redirect to the block listing,
    // because we need to remove cached block contents for each cache backend.
    Cache::invalidateTags(array('content' => TRUE));
    if ($this->getRequest()->isXmlHttpRequest()) {
      $response = new AjaxResponse();
      $response->addCommand(new CloseDialogCommand());
      $response->addCommand(new LayoutComponentReload($this->entity));

      return $response;
    }
    $form_state['redirect_route'] = array(
      'route_name' => 'layout.configure',
      'route_parameters' => array(
        'layout' => $form_state['layout'],
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
  public function getLayoutComponentUniqueMachineName(LayoutComponent $component) {
    $suggestion = $component->getPlugin()->getMachineNameSuggestion();

    // Get all the components which starts with the suggested machine name.
    $query = \Drupal::service('entity.manager')->getStorage('layout_component')->getQuery();
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
