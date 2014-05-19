<?php

/**
 * @file
 * Contains \Drupal\layout\Plugin\LayountContainerInterface.
 */

namespace Drupal\layout\Plugin;

use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Plugin\PluginFormInterface;

/**
 * Provides an interface for PageVariant plugins.
 */
interface LayoutContainerInterface extends PluginInspectionInterface, ConfigurablePluginInterface, PluginFormInterface {
}
