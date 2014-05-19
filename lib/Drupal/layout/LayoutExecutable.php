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
 * @defgroup views_objects Objects that represent a View or part of a view
 * @{
 * These objects are the core of Views do the bulk of the direction and
 * storing of data. All database activity is in these objects.
 */

/**
 * An object to contain all of the data to generate a view, plus the member
 * functions to build the view query, execute the query and render the output.
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
   * Any arguments that have been passed into the view.
   *
   * @var array
   */
  public $args = array();

  /**
   * An array of build info.
   *
   * @var array
   */
  public $build_info = array();

  /**
   * Used to store views that were previously running if we recurse.
   *
   * @var array
   */
  public $old_layout = array();

  /**
   * The current used display plugin.
   *
   * @var \Drupal\layout\Plugin\layout\LayoutPluginBase
   */
  public $display_handler;

  /**
   * Allow to override the url of the current view.
   *
   * @var string
   */
  public $override_url = NULL;

  /**
   * Allow to override the path used for generated urls.
   *
   * @var string
   */
  public $override_path = NULL;

  // Handlers which are active on this view.
  /**
   * Stores the argument handlers which are initialized on this view.
   *
   * An array containing Drupal\views\Plugin\views\argument\ArgumentPluginBase
   * objects.
   *
   * @var array
   */
  public $argument;

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
   * A unique identifier which allows to update multiple views output via js.
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
   * Should the admin links be shown on the rendered view.
   *
   * @var bool
   */
  protected $showAdminLinks;

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
   * Set the arguments that come to this view. Usually from the URL
   * but possibly from elsewhere.
   */
  public function setArguments($args) {
    $this->args = $args;
  }

  /**
   * Set the display for this view and initialize the display handler.
   */
  public function initDisplay() {
    if (isset($this->current_display)) {
      return TRUE;
    }

    $this->current_display = 'default';
    $this->display_handler = Layouts::pluginManager('layout_display')->getInstance();

    return TRUE;
  }

  /**
   * Get the first display that is accessible to the user.
   *
   * @param array|string $displays
   *   Either a single display id or an array of display ids.
   *
   * @return string
   *   The first accessible display id, at least default.
   */
  public function chooseDisplay($displays) {
    if (!is_array($displays)) {
      return $displays;
    }

    $this->initDisplay();

    foreach ($displays as $display_id) {
      if ($this->displayHandlers->get($display_id)->access($this->user)) {
        return $display_id;
      }
    }

    return 'default';
  }

  /**
   * Gets the current display plugin.
   *
   * @return \Drupal\views\Plugin\views\display\DisplayPluginBase
   */
  public function getDisplay() {
    if (!isset($this->display_handler)) {
      $this->initDisplay();
    }

    return $this->display_handler;
  }

  /**
   * Sets the current display.
   *
   * @param string $display_id
   *   The ID of the display to mark as current.
   *
   * @return bool
   *   TRUE if the display was correctly set, FALSE otherwise.
   */
  public function setDisplay($display_id = NULL) {
    // If we have not already initialized the display, do so.
    if (!isset($this->current_display)) {
      // This will set the default display and instantiate the default display
      // plugin.
      $this->initDisplay();
    }

    // If no display ID is passed, we either have initialized the default or
    // already have a display set.
    if (!isset($display_id)) {
      return TRUE;
    }

    $display_id = $this->chooseDisplay($display_id);

    // Ensure the requested display exists.
    if (!$this->displayHandlers->has($display_id)) {
      debug(format_string('setDisplay() called with invalid display ID "@display".', array('@display' => $display_id)));
      return FALSE;
    }

    // Reset if the display has changed. It could be called multiple times for
    // the same display, especially in the UI.
    if ($this->current_display != $display_id) {
      // Set the current display.
      $this->current_display = $display_id;

      // Reset the style and row plugins.
      $this->style_plugin = NULL;
      $this->plugin_name = NULL;
      $this->rowPlugin = NULL;
    }

    if ($display = $this->displayHandlers->get($display_id)) {
      // Set a shortcut.
      $this->display_handler = $display;
      return TRUE;
    }

    return FALSE;
  }

  /**
   * Creates a new display and a display handler instance for it.
   *
   * @param string $plugin_id
   *   (optional) The plugin type from the Views plugin annotation. Defaults to
   *   'page'.
   * @param string $title
   *   (optional) The title of the display. Defaults to NULL.
   * @param string $id
   *   (optional) The ID to use, e.g., 'default', 'page_1', 'block_2'. Defaults
   *   to NULL.
   *
   * @return \Drupal\views\Plugin\views\display\DisplayPluginBase
   *   A new display plugin instance if executable is set, the new display ID
   *   otherwise.
   */
  public function newDisplay($plugin_id = 'page', $title = NULL, $id = NULL) {
    $this->initDisplay();

    $id = $this->storage->addDisplay($plugin_id, $title, $id);
    $this->displayHandlers->addInstanceId($id);

    $display = $this->displayHandlers->get($id);
    $display->newDisplay();
    return $display;
  }

  /**
   * Acquire and attach all of the handlers.
   */
  public function initHandlers() {
    $this->initDisplay();
  }

  /**
   * Attach all of the handlers for each type.
   *
   * @param $key
   *   One of 'argument', 'field', 'sort', 'filter', 'relationship'
   * @param $info
   *   The $info from getHandlerTypes for this object.
   */
  protected function _initHandler($key, $info) {
    // Load the requested items from the display onto the object.
    $this->$key = $this->display_handler->getHandlers($key);

    // This reference deals with difficult PHP indirection.
    $handlers = &$this->$key;

    // Run through and test for accessibility.
    foreach ($handlers as $id => $handler) {
      if (!$handler->access($this->user)) {
        unset($handlers[$id]);
      }
    }
  }

  /**
   * Build all the arguments.
   */
  protected function _buildArguments() {
    // Initially, we want to build sorts and fields. This can change, though,
    // if we get a summary view.
    if (empty($this->argument)) {
      return TRUE;
    }

    // build arguments.
    $position = -1;
    $substitutions = array();
    $status = TRUE;

    // Get the title.
    $title = $this->display_handler->getOption('title');

    // Iterate through each argument and process.
    foreach ($this->argument as $id => $arg) {
      $position++;
      $argument = $this->argument[$id];

      if ($argument->broken()) {
        continue;
      }

      $argument->setRelationship();

      $arg = isset($this->args[$position]) ? $this->args[$position] : NULL;
      $argument->position = $position;

      if (isset($arg) || $argument->hasDefaultArgument()) {
        if (!isset($arg)) {
          $arg = $argument->getDefaultArgument();
          // make sure default args get put back.
          if (isset($arg)) {
            $this->args[$position] = $arg;
          }
          // remember that this argument was computed, not passed on the URL.
          $argument->is_default = TRUE;
        }

        // Set the argument, which will also validate that the argument can be set.
        if (!$argument->setArgument($arg)) {
          $status = $argument->validateFail($arg);
          break;
        }

        if ($argument->isException()) {
          $arg_title = $argument->exceptionTitle();
        }
        else {
          $arg_title = $argument->getTitle();
          $argument->query($this->display_handler->useGroupBy());
        }

        // Add this argument's substitution
        $substitutions['%' . ($position + 1)] = $arg_title;
        $substitutions['!' . ($position + 1)] = strip_tags(decode_entities($arg));

        // Test to see if we should use this argument's title
        if (!empty($argument->options['title_enable']) && !empty($argument->options['title'])) {
          $title = $argument->options['title'];
        }
      }
      else {
        // determine default condition and handle.
        $status = $argument->defaultAction();
        break;
      }

      // Be safe with references and loops:
      unset($argument);
    }

    // set the title in the build info.
    if (!empty($title)) {
      $this->build_info['title'] = $title;
    }

    // Store the arguments for later use.
    $this->build_info['substitutions'] = $substitutions;

    return $status;
  }

  /**
   * Internal method to build an individual set of handlers.
   *
   * @todo Some filter needs this function, even it is internal.
   *
   * @param string $key
   *    The type of handlers (filter etc.) which should be iterated over to
   *    build the relationship and query information.
   */
  public function _build($key) {
  }

  /**
   * Execute the view's query.
   *
   * @param string $display_id
   *   The machine name of the display, which should be executed.
   *
   * @return bool
   *   Return whether the executing was successful, for example an argument
   *   could stop the process.
   */
  public function execute($display_id = NULL) {
    $this->executed = TRUE;
  }

  /**
   * Render this view for a certain display.
   *
   * Note: You should better use just the preview function if you want to
   * render a view.
   *
   * @param string $display_id
   *   The machine name of the display, which should be rendered.
   *
   * @return string|null
   *   Return the output of the rendered view or NULL if something failed in the process.
   */
  public function render($display_id = NULL) {
    $this->execute($display_id);

    // Check to see if the build failed.
    if (!empty($this->build_info['fail'])) {
      return;
    }
    if (!empty($this->build_info['denied'])) {
      return;
    }

    $exposed_form = $this->display_handler->getPlugin('exposed_form');
    $exposed_form->preRender($this->result);

    $module_handler = \Drupal::moduleHandler();

    // Check for already-cached output.
    if (!empty($this->live_preview)) {
      $cache = FALSE;
    }
    else {
      $cache = $this->display_handler->getPlugin('cache');
    }

    if ($cache && $cache->cacheGet('output')) {
    }
    else {
      if ($cache) {
        $cache->cacheStart();
      }

      // Run preRender for the pager as it might change the result.
      if (!empty($this->pager)) {
        $this->pager->preRender($this->result);
      }

      // Initialize the style plugin.
      $this->initStyle();

      if (!isset($this->response)) {
        // Set the response so other parts can alter it.
        $this->response = new Response('', 200);
      }

      // Give field handlers the opportunity to perform additional queries
      // using the entire resultset prior to rendering.
      if ($this->style_plugin->usesFields()) {
        foreach ($this->field as $id => $handler) {
          if (!empty($this->field[$id])) {
            $this->field[$id]->preRender($this->result);
          }
        }
      }

      $this->style_plugin->preRender($this->result);

      // Let each area handler have access to the result set.
      $areas = array('header', 'footer');
      // Only call preRender() on the empty handlers if the result is empty.
      if (empty($this->result)) {
        $areas[] = 'empty';
      }
      foreach ($areas as $area) {
        foreach ($this->{$area} as $handler) {
          $handler->preRender($this->result);
        }
      }

      // Let modules modify the view just prior to rendering it.
      $module_handler->invokeAll('views_pre_render', array($this));

      // Let the themes play too, because pre render is a very themey thing.
      if (isset($GLOBALS['base_theme_info']) && isset($GLOBALS['theme'])) {
        foreach ($GLOBALS['base_theme_info'] as $base) {
          $module_handler->invoke($base->getName(), 'views_pre_render', array($this));
        }

        $module_handler->invoke($GLOBALS['theme'], 'views_pre_render', array($this));
      }

      $this->display_handler->output = $this->display_handler->render();
      if ($cache) {
        $cache->cacheSet('output');
      }
    }

    $exposed_form->postRender($this->display_handler->output);

    if ($cache) {
      $cache->postRender($this->display_handler->output);
    }

    // Let modules modify the view output after it is rendered.
    $module_handler->invokeAll('views_post_render', array($this, &$this->display_handler->output, $cache));

    // Let the themes play too, because post render is a very themey thing.
    if (isset($GLOBALS['base_theme_info']) && isset($GLOBALS['theme'])) {
      foreach ($GLOBALS['base_theme_info'] as $base) {
        $module_handler->invoke($base->getName(), 'views_post_render', array($this));
      }

      $module_handler->invoke($GLOBALS['theme'], 'views_post_render', array($this));
    }

    return $this->display_handler->output;
  }

  /**
   * Execute the given display, with the given arguments.
   * To be called externally by whatever mechanism invokes the view,
   * such as a page callback, hook_block, etc.
   *
   * This function should NOT be used by anything external as this
   * returns data in the format specified by the display. It can also
   * have other side effects that are only intended for the 'proper'
   * use of the display, such as setting page titles.
   *
   * If you simply want to view the display, use View::preview() instead.
   */
  public function executeDisplay($display_id = NULL, $args = array()) {
    if (empty($this->current_display) || $this->current_display != $this->chooseDisplay($display_id)) {
      if (!$this->setDisplay($display_id)) {
        return NULL;
      }
    }

    $this->preExecute($args);

    // Execute the view
    $output = $this->display_handler->execute();

    $this->postExecute();
    return $output;
  }

  /**
   * Preview the given display, with the given arguments.
   *
   * To be called externally, probably by an AJAX handler of some flavor.
   * Can also be called when views are embedded, as this guarantees
   * normalized output.
   *
   * This function does not do any access checks on the view. It is the
   * responsibility of the caller to check $view->access() or implement other
   * access logic. To render the view normally with access checks, use
   * views_embed_view() instead.
   */
  public function preview($display_id = NULL, $args = array()) {
    if (empty($this->current_display) || ((!empty($display_id)) && $this->current_display != $display_id)) {
      if (!$this->setDisplay($display_id)) {
        return FALSE;
      }
    }

    $this->preview = TRUE;
    $this->preExecute($args);
    // Preview the view.
    $output = $this->display_handler->preview();

    $this->postExecute();
    return $output;
  }

  /**
   * Run attachments and let the display do what it needs to do prior
   * to running.
   */
  public function preExecute($args = array()) {
    $this->old_view[] = views_get_current_view();
    views_set_current_view($this);
    $display_id = $this->current_display;

    // Prepare the view with the information we have, but only if we were
    // passed arguments, as they may have been set previously.
    if ($args) {
      $this->setArguments($args);
    }

    // Let modules modify the view just prior to executing it.
    \Drupal::moduleHandler()->invokeAll('views_pre_view', array($this, $display_id, &$this->args));

    // Allow hook_views_pre_view() to set the dom_id, then ensure it is set.
    $this->dom_id = !empty($this->dom_id) ? $this->dom_id : hash('sha256', $this->storage->id() . REQUEST_TIME . mt_rand());

    // Allow the display handler to set up for execution
    $this->display_handler->preExecute();
  }

  /**
   * Unset the current view, mostly.
   */
  public function postExecute() {
    // unset current view so we can be properly destructed later on.
    // Return the previous value in case we're an attachment.

    if ($this->old_view) {
      $old_view = array_pop($this->old_view);
    }

    views_set_current_view(isset($old_view) ? $old_view : FALSE);
  }

  /**
   * Run attachment displays for the view.
   */
  public function attachDisplays() {
    if (!empty($this->is_attachment)) {
      return;
    }

    if (!$this->display_handler->acceptAttachments()) {
      return;
    }

    $this->is_attachment = TRUE;
    // Find out which other displays attach to the current one.
    foreach ($this->display_handler->getAttachedDisplays() as $id) {
      // Create a clone for the attachments to manipulate. 'static' refers to the current class name.
      $cloned_view = new static($this->storage, $this->user);
      $this->displayHandlers->get($id)->attachTo($cloned_view, $this->current_display);
    }
    $this->is_attachment = FALSE;
  }

  /**
   * Returns default menu links from the view and the named display handler.
   *
   * @param string $display_id
   *   A display ID.
   * @param array $links
   *   An array of default menu link items passed from
   *   views_menu_link_defaults_alter().
   *
   * @return array|bool
   */
  public function executeHookMenuLinkDefaults($display_id = NULL, &$links = array()) {
    // Prepare the view with the information we have. This was probably already
    // called, but it's good to be safe.
    if (!$this->setDisplay($display_id)) {
      return FALSE;
    }

    // Execute the hook.
    if (isset($this->display_handler)) {
      return $this->display_handler->executeHookMenuLinkDefaults($links);
    }
  }

  /**
   * Determine if the given user has access to the view. Note that
   * this sets the display handler if it hasn't been.
   */
  public function access($displays = NULL, $account = NULL) {
    // No one should have access to disabled views.
    if (!$this->storage->status()) {
      return FALSE;
    }

    if (!isset($this->current_display)) {
      $this->initDisplay();
    }

    if (!$account) {
      $account = $this->user;
    }

    // We can't use choose_display() here because that function
    // calls this one.
    $displays = (array)$displays;
    foreach ($displays as $display_id) {
      if ($this->displayHandlers->has($display_id)) {
        if (($display = $this->displayHandlers->get($display_id)) && $display->access($account)) {
          return TRUE;
        }
      }
    }

    return FALSE;
  }

  /**
   * Returns the valid types of plugins that can be used.
   *
   * @return array
   *   An array of plugin type strings.
   */
  public static function getPluginTypes($type = NULL) {
  }

  /**
   * Provide a full array of possible theme functions to try for a given hook.
   *
   * @param string $hook
   *   The hook to use. This is the base theme/template name.
   *
   * @return array
   *   An array of theme hook suggestions.
   */
  public function buildThemeFunctions($hook) {
    $themes = array();
    $display = isset($this->display_handler) ? $this->display_handler->display : NULL;
    $id = $this->storage->id();

    if ($display) {
      $themes[] = $hook . '__' . $id . '__' . $display['id'];
      $themes[] = $hook . '__' . $display['id'];
      // Add theme suggestions for each single tag.
      foreach (Tags::explode($this->storage->get('tag')) as $tag) {
        $themes[] = $hook . '__' . preg_replace('/[^a-z0-9]/', '_', strtolower($tag));
      }

      if ($display['id'] != $display['display_plugin']) {
        $themes[] = $hook . '__' . $id . '__' . $display['display_plugin'];
        $themes[] = $hook . '__' . $display['display_plugin'];
      }
    }
    $themes[] = $hook . '__' . $id;
    $themes[] = $hook;

    return $themes;
  }

  /**
   * Calculates dependencies for the view.
   *
   * @see \Drupal\views\Entity\View::calculateDependencies()
   *
   * @return array
   *   An array of dependencies grouped by type (module, theme, entity).
   */
  public function calculateDependencies() {
    return $this->storage->calculateDependencies();
  }

}
