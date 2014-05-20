<?php

/**
 * @file
 * Definition of Drupal\layout\LayoutExecutable.
 */

namespace Drupal\layout;

use Drupal\Core\DependencyInjection\DependencySerialization;
use Drupal\Core\Session\AccountInterface;
use Drupal\Component\Utility\Tags;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Drupal\layout\LayoutStorageInterface;


/**
 * An object to contain all of the data to generate a layout.
 */
class LayoutExecutable extends DependencySerialization {

  /**
   * The config entity in which the view is stored.
   *
   * @var \Drupal\layout\Entity\LayoutStorageInterface
   */
  public $storage;

  /**
   * Whether or not the layout has been built.
   *
   * @todo Group with other static properties.
   *
   * @var bool
   */
  public $built = FALSE;

  /**
   * Whether the layout has been executed/query has been run.
   *
   * @todo Group with other static properties.
   *
   * @var bool
   */
  public $executed = FALSE;

  /**
   * An array of build info.
   *
   * @var array
   */
  public $build_info = array();

  /**
   * Stores the current response object.
   *
   * @var \Symfony\Component\HttpFoundation\Response
   */
  protected $response = NULL;

  /**
   * Stores the current request object.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * Does this view already have loaded it's handlers.
   *
   * @todo Group with other static properties.
   *
   * @var bool
   */
  public $inited;

  /**
   * A unique identifier which allows to update multiple layout output via js.
   *
   * @var string
   */
  public $dom_id;

  /**
   * A render array container to store render related information.
   *
   * For example you can alter the array and attach some css/js via the
   * #attached key. This is the required way to add custom css/js.
   *
   * @var array
   *
   * @see drupal_process_attached
   */
  public $element = array(
    '#attached' => array(
      'css' => array(),
      'js' => array(),
      'library' => array(),
    ),
  );

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $user;

  /**
   * Constructs a new ViewExecutable object.
   *
   * @param \Drupal\layout\LayoutStorageInterface $storage
   *   The view config entity the actual information is stored on.
   * @param \Drupal\Core\Session\AccountInterface $user
   *   The current user.
   */
  public function __construct(LayoutStorageInterface $storage, AccountInterface $user) {
    // Reference the storage and the executable to each other.
    $this->storage = $storage;
    $this->storage->set('executable', $this);
    $this->user = $user;
  }

  /**
   * @todo.
   */
  public function save() {
    $this->storage->save();
  }

  /**
   * Calculates dependencies for the view.
   *
   * @see \Drupal\layout\Entity\Layout::calculateDependencies()
   *
   * @return array
   *   An array of dependencies grouped by type (module, theme, entity).
   */
  public function calculateDependencies() {
    return $this->storage->calculateDependencies();
  }

}
