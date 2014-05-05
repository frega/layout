<?php

/**
 * @file
 * Contains \Drupal\layout\LayoutAccessController.
 */

namespace Drupal\layout;

use Drupal\Core\Entity\EntityAccessController;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Language\Language;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the access controller for the layout entity type.
 */
class LayoutAccessController extends EntityAccessController {

  /**
   * {@inheritdoc}
   */
  public function checkAccess(EntityInterface $entity, $operation, $langcode, AccountInterface $account) {
    return $operation == 'view' || parent::checkAccess($entity, $operation, $langcode, $account);
  }

}
