<?php
/**
 * @file
 * Contains \Drupal\layout\LayoutExecutableFactory.
 */


namespace Drupal\layout;

use Drupal\Core\Session\AccountInterface;
use Drupal\layout\LayoutStorageInterface;
use Drupal\layout\LayoutExecutable;


/**
 * Defines the cache backend factory.
 */
class LayoutExecutableFactory {

  /**
   * Stores the current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $user;

  /**
   * Constructs a new ViewExecutableFactory
   *
   * @param \Drupal\Core\Session\AccountInterface $user
   *   The current user.
   */
  public function __construct(AccountInterface $user) {
    $this->user = $user;
  }

  /**
   * Instantiates a LayoutExecutable class.
   *
   * @param \Drupal\layout\LayoutStorageInterface $view
   *   A layout entity instance.
   *
   * @return \Drupal\layout\LayoutExecutable
   *   A LayoutExecutable instance.
   */
  public function get(LayoutStorageInterface $layout) {
    return new LayoutExecutable($layout, $this->user);
  }

}
