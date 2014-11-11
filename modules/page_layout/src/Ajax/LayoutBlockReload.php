<?php

/**
 * @file
 * Contains \Drupal\page_layout\Ajax\BaseCommand.
 */

namespace Drupal\page_layout\Ajax;

use Drupal\core\Block\BlockPluginInterface;
use Drupal\Core\Ajax\CommandInterface;

use Drupal\page_layout\PageLayout;


/**
 * Base AJAX command that only exists simplify Edit's actual AJAX commands.
 */
class LayoutBlockReload implements CommandInterface {

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
  public function __construct(BlockPluginInterface $block) {
    $this->command = 'layoutBlockReload';
    $this->data = PageLayout::blockToArray($block);
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
