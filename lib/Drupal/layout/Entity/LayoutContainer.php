<?php

/**
 * @file
 * Definition of Drupal\layout\Entity\Layout.
 */

namespace Drupal\layout\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use \Drupal\Component\Serialization\Json;


/**
 * Defines a Layout container
 *
 * @ConfigEntityType(
 *   id = "layout_container",
 *   label = @Translation("Layout Container"),
 *   controllers = {
 *     "access" = "Drupal\layout\LayoutAccessController",
 *     "form" = {
 *       "default" = "Drupal\layout\LayoutContainerFormController",
 *       "delete_confirm" = "Drupal\layout\Form\LayoutContainerDeleteConfirmForm"
 *     }
 *   },
 *   admin_permission = "administer layouts",
 *   config_prefix = "layout_container",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label"
 *   },
 *   links = {
 *   }
 * )
 */
class LayoutContainer extends ConfigEntityBase {
  /**
   * The unique ID of the layout.
   *
   * @var string
   */
  public $id = NULL;

  /**
   * The label of the layout region.
   *
   * @var string
   */
  public $label;

  /**
   * The administrative label of the layout region.
   *
   * @var string
   */
  public $admin_label;

  /**
   * Settings
   *
   * @var string
   */
  public $settings;
}
