<?php
/**
 * @file
 * Contains \Drupal\views\EventSubscriber\RouteSubscriber.
 */

namespace Drupal\layout\EventSubscriber;

use Drupal\layout\Layout;

use Drupal\Core\Page\HtmlPage;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\KeyValueStore\StateInterface;
use Drupal\Core\Routing\RouteSubscriberBase;
use Drupal\Core\Routing\RoutingEvents;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Builds up the routes of all page layouts.
 *
 * The general idea is to execute first all alter hooks to determine which
 * routes are overridden by layout. This information is used to determine which
 * layout have to be added by layout in the dynamic event.
 *
 * Additional to adding routes it also changes the htmlpage response code.
 *
 * @see \Drupal\layout\Plugin\layout\display\PathPluginBase
 */
class RouteSubscriber extends RouteSubscriberBase {
  /**
   * Stores a list of view,display IDs which haven't be used in the alter event.
   *
   * @var array
   */
  protected $layoutDisplayPairs;

  /**
   * The view storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $layoutStorage;

  /**
   * The state key value store.
   *
   * @var \Drupal\Core\KeyValueStore\StateInterface
   */
  protected $state;

  /**
   * Stores an array of route names keyed by view_id.display_id.
   *
   * @var array
   */
  protected $layoutRouteNames = array();

  /**
   * Constructs a \Drupal\views\EventSubscriber\RouteSubscriber instance.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   * @param \Drupal\Core\KeyValueStore\StateInterface $state
   *   The state key value store.
   */
  public function __construct(EntityManagerInterface $entity_manager, StateInterface $state) {
    $this->layoutStorage = $entity_manager->getStorage('layout');
    $this->state = $state;
  }

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection, $provider) {
    $query = $this->layoutStorage->getQuery();
    $layout_ids = $query->execute();
    $layouts = $this->layoutStorage->loadMultiple($layout_ids);
    foreach ($layouts as $layout) {
      if ($layout->get('path')) {
        $route = new Route(
        // Path to attach this route to:
          $layout->get('path'),
          // Route defaults:
          array(
            '_content' => '\Drupal\layout\LayoutViewController::view',
            '_title' => $layout->label(),
            '_layout' => $layout->id(),
          ),
          // Route requirements:
          array(
            '_permission'  => 'access content',
          )
        );

        // Add the route under the name 'layout.content'
        // @todo: let's avoid overriding system/layout stuff in the future :|
        $collection->add('layout.' . $layout->id(), $route);
      }
    }
  }
}
