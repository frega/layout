<?php

namespace Drupal\layout;


class Layouts {
  /**
  * Returns the layout executable factory service.
  *
  * @return \Drupal\layout\LayoutExecutableFactory
  *   Returns a views executable factory.
  */
  public static function executableFactory() {
    return \Drupal::service('layout.executable');
  }

  /**
   * Returns the plugin manager for a certain views plugin type.
   *
   * @param string $type
   *   The plugin type, for example filter.
   *
   * @return \Drupal\views\Plugin\ViewsPluginManager
   */
  public static function pluginManager($type) {
    return \Drupal::service('plugin.manager.layout.' . $type);
  }

}
