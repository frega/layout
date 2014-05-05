<?php

/**
 * @file
 * Definition of Drupal\layout\Entity\Layout.
 */

namespace Drupal\layout\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Component\Utility\Json;


/**
 * Defines a Layout master configuration entity class.
 *
 * @ConfigEntityType(
 *   id = "layout_master",
 *   label = @Translation("Layout Master (Template/Region)"),
 *   controllers = {
 *     "access" = "Drupal\layout\LayoutAccessController",
 *     "form" = {
 *       "default" = "Drupal\layout\LayoutContainerFormController"
 *     }
 *   },
 *   admin_permission = "administer layouts",
 *   config_prefix = "layout_master",
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
  public $template;

  /**
   * Containers
   *
   * @var string
   */
  public $containers;
}
