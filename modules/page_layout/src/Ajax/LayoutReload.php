<?php

/**
 * @file
 * Contains \Drupal\page_layout\Ajax\BaseCommand.
 */

namespace Drupal\page_layout\Ajax;

use Drupal\Core\Ajax\CommandInterface;

use Drupal\page_layout\PageLayout;
use Drupal\page_manager\PageInterface;
use Drupal\page_manager\Plugin\PageVariantInterface;


/**
 * Base AJAX command that only exists simplify Edit's actual AJAX commands.
 */
class LayoutReload implements CommandInterface {

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
  public function __construct(PageInterface $page, PageVariantInterface $page_variant) {
    $this->command = 'layoutReload';
    $this->data = PageLayout::getLayoutPageVariantClientData($page, $page_variant);
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
