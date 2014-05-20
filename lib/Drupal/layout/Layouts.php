<?php

namespace Drupal\layout;


class Layouts {
  /**
   * Returns the plugin manager for a certain layout plugin type.
   *
   * @param string $type
   *   The plugin type, for example filter.
   *
   * @return \Drupal\layout\Plugin\LayoutPluginManager
   */
  public static function pluginManager($type) {
    return \Drupal::service('plugin.manager.layout.' . $type);
  }

  /**
   * Returns the layout executable factory service.
   *
   * @return \Drupal\layout\LayoutExecutableFactory
   *   Returns layout executable factory.
   */
  public static function executableFactory() {
    return \Drupal::service('layout.executable');
  }

}
