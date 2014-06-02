<?php

/**
 * @file
 * Contains \Drupal\layout\Ajax\BaseCommand.
 */

namespace Drupal\layout\Ajax;

use Drupal\Core\Ajax\CommandInterface;

use Drupal\layout\Layouts;
use Drupal\layout\Plugin\LayoutPageVariantInterface;
use Drupal\layout\Plugin\LayoutRegionPluginInterface;
use Drupal\page_manager\PageInterface;


/**
 * Base AJAX command that only exists simplify Edit's actual AJAX commands.
 */
class LayoutRegionReload implements CommandInterface {

  /**
   * The name of the command.
   *
   * @var string
   */
  protected $command;

  /**
   * The data to pass on to the client side.
   *
   * @var string
   */
  protected $data;

  /**
   * Constructs a BaseCommand object.
   *
   * @param string $data
   *   The data to pass on to the client side.
   */
  public function __construct(LayoutPageVariantInterface $page_variant, LayoutRegionPluginInterface $layout_region) {
    $this->command = 'layoutRegionReload';
    $data = Layouts::getGroupedBlockArrays($page_variant);
    $region = array();
    foreach ($data['regions'] as $nr => $region) {
      if ($region['id'] === $layout_region->id()) {
        $this->data = $region;
        break;
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    return array(
      'command' => $this->command,
      'data' => $this->data,
    );
  }

}
