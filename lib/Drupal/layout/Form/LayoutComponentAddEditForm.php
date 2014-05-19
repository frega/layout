<?php

/**
 * @file
 * Contains \Drupal\layout\ComponentAddController.
 */

namespace Drupal\layout\Form;

use Drupal\block\Controller\BlockAddController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Controller for building the component instance add form.
 */
class LayoutComponentAddEditForm extends BlockAddController {

  /**
   * Build the component instance add form.
   *
   * @param string $plugin_id
   *   The plugin ID for the component instance.
   * @param string $layout
   *   The name of the layout for the component instance.
   *
   * @return array
   *   The component instance edit form.
   */
  public function addForm($plugin_id, $layout = NULL, $layout_region_id = NULL) {
    $layout_entity = layout_load($layout);
    if (!is_object($layout_entity)) {
      throw new BadRequestHttpException('Invalid layout id provided');
    }

    if (!$layout_entity->getLayoutContainer($layout_region_id)) {
      throw new BadRequestHttpException('Invalid layout region id provided');
    }

    // Create a component entity.
    $entity = \Drupal::service('entity.manager')->getStorage('layout_component')->create(array(
      'plugin' => $plugin_id,
      'layout' => $layout,
      'container' => $layout_region_id
    ));
    return $this->entityFormBuilder()->getForm($entity);
  }

  public function editForm($plugin_id, $layout = NULL, $layout_region_id = NULL) {
    $layout_entity = layout_load($layout);
    if (!is_object($layout_entity)) {
      throw new BadRequestHttpException('Invalid layout id provided');
    }

    if (!$layout_entity->getLayoutContainer($layout_region_id)) {
      throw new BadRequestHttpException('Invalid layout region id provided');
    }

    $query = \Drupal::service('entity.manager')->getStorage('layout_component')->getQuery();
    $query->condition('layout', $layout_entity->id(), 'CONTAINS');
    $query->condition('container', $layout_region_id, 'CONTAINS');
    $query->condition('id', $plugin_id, 'CONTAINS');
    $component_ids = $query->execute();


    if (!sizeof($component_ids) || sizeof($component_ids) > 1) {
      throw new BadRequestHttpException('Invalid plugin id provided');
    }

    reset($component_ids);
    $id = key($component_ids);
    $entity = layout_component_load($id);
    return $this->entityFormBuilder()->getForm($entity);
  }
}
