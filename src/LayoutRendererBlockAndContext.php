<?php

/**
 * @file
 * Contains \Drupal\layout\LayoutRendererBlockAndContext.
 */

namespace Drupal\layout;

use Drupal\Component\Plugin\ContextAwarePluginInterface;
use Drupal\Core\Plugin\Context\ContextHandlerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\layout\Plugin\Layout\LayoutBlockAndContextProviderInterface;
use Drupal\layout\Plugin\Layout\LayoutInterface;
use Drupal\layout\Plugin\LayoutRegion\LayoutRegionInterface;
use Drupal\search_api\Plugin\ConfigurablePluginInterface;

/**
 * Renders a layout using a block and context provider.
 */
class LayoutRendererBlockAndContext {

  /**
   * The context handler.
   *
   * @var \Drupal\Core\Plugin\Context\ContextHandlerInterface
   */
  protected $contextHandler;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $account;

  /**
   * Constructs a new BlockPageVariant.
   *
   * @param \Drupal\Core\Plugin\Context\ContextHandlerInterface $context_handler
   *   The context handler.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The current user.
   */
  public function __construct(ContextHandlerInterface $context_handler, AccountInterface $account) {
    $this->contextHandler = $context_handler;
    $this->account = $account;
  }

  /**
   * Builds the layout.
   *
   * @param \Drupal\layout\Plugin\Layout\LayoutInterface $layout
   *   The layout to render.
   * @param \Drupal\layout\Plugin\Layout\LayoutBlockAndContextProviderInterface $provider
   *   The block and context provider needed to build the layout.
   *
   * @return array
   *   The render array.
   */
  public function build(LayoutInterface $layout, LayoutBlockAndContextProviderInterface $provider) {
    $regions = $provider->getLayoutRegions();
    $renderArray = array();
    $rootRegions = array();
    // Find rootRegions - @note we are doing it this way because *nesting* getLayoutRegions-calls
    // resets the internal iterator apparently.
    foreach ($regions as $region) {
      if (!$region->getParentRegionId()) {
        $rootRegions[$region->getConfiguration()['region_id']] = $region;
      }
    }

    foreach ($rootRegions as $region_id => $region) {
      $renderArray[$region_id] = $this->buildRegion($region, $provider);
    }

    return array(
      '#theme' => array($layout->getPluginDefinition()['theme'], 'layout'),
      '#regions' => $renderArray,
    );
  }

  /**
   * Builds the layout region.
   *
   * @param \Drupal\layout\Plugin\LayoutRegion\LayoutRegionInterface $region
   *   The layout to render.
   * @param \Drupal\layout\Plugin\Layout\LayoutBlockAndContextProviderInterface $provider
   *   The block and context provider needed to build the layout region.
   *
   * @return array
   *   The render array.
   */
  public function buildRegion(LayoutRegionInterface $region, LayoutBlockAndContextProviderInterface $provider) {
    $contexts = $provider->getContexts();
    $blocksInRegion = $provider->getBlocksByRegion($region->id());
    /** @var $blocksInRegion \Drupal\block\BlockPluginInterface[] */
    $renderArray = array();
    foreach ($blocksInRegion as $id => $block) {
      if ($block instanceof ContextAwarePluginInterface) {
        $mapping = array();
        if ($block instanceof ConfigurablePluginInterface) {
          $configuration = $block->getConfiguration();
          if (isset($configuration['context_mapping'])) {
            $mapping = array_flip($configuration['context_mapping']);
          }
        }
        $this->contextHandler->applyContextMapping($block, $contexts, $mapping);
      }

      if ($block->access($this->account)) {
        $block_render_array = $block->build();
        $block_name = $this->htmlId($id);
        $block_render_array['#prefix'] = '<div class="' . $block_name . '">';
        $block_render_array['#suffix'] = '</div>';

        $renderArray[] = $block_render_array;
      }
    }

    $regions = $this->getSubRegions($region, $provider);
    $subregionsRenderArray = array();
    if (count($regions)) {
      foreach ($regions as $region) {
        $subregionsRenderArray[] = $this->buildRegion($region, $provider);
      }
    }

    return array(
      '#theme' => $region->getPluginDefinition()['theme'],
      '#blocks' => $renderArray,
      '#regions' => $subregionsRenderArray,
      '#region' => $this,
      '#region_id' => $region->id(),
    );
  }

  public function getSubRegions(LayoutRegionInterface $parent_region, LayoutBlockAndContextProviderInterface $provider) {
    $regions = $provider->getLayoutRegions();
    $filtered = array();
    foreach ($regions as $region) {
      if ($region->getParentRegionId() === $parent_region->id()) {
        $filtered[] = $region;
      }
    }
    return $filtered;
  }

  /**
   * Returns a HTML safe ID.
   */
  protected function htmlId($id) {
    return drupal_html_class("block-$id");
  }

}
