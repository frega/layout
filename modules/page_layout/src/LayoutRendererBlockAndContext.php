<?php

/**
 * @file
 * Contains \Drupal\page_layout\LayoutRendererBlockAndContext.
 */

namespace Drupal\layout;

use Drupal\Component\Plugin\ContextAwarePluginInterface;
use Drupal\Component\Utility\String;
use Drupal\Core\Plugin\Context\ContextHandlerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\page_layout\LayoutBlockAndContextProviderInterface;
use Drupal\page_layout\Plugin\LayoutRegion\LayoutRegionInterface;
use Drupal\layout_plugin\Plugin\Layout\LayoutInterface;

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
   * @param \Drupal\layout_plugin\Plugin\Layout\LayoutInterface $layout
   *   The layout to render.
   * @param \Drupal\page_layout\LayoutBlockAndContextProviderInterface $provider
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
    // resets the internal iterator.
    foreach ($regions as $region) {
      if (!$region->getParentRegionId()) {
        $rootRegions[$region->getConfiguration()['region_id']] = $region;
      }
    }

    // Loop regions and build them, if needed, recursively.
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
   * @param \Drupal\page_layout\Plugin\LayoutRegion\LayoutRegionInterface $region
   *   The layout region to render.
   * @param \Drupal\page_layout\LayoutBlockAndContextProviderInterface $provider
   *   The block and context provider needed to build the layout region.
   *
   * @return array
   *   The render array.
   */
  public function buildRegion(LayoutRegionInterface $region, LayoutBlockAndContextProviderInterface $provider) {
    $render_array = $this->buildRegionBlocks($region, $provider);
    // A layout region should only have subregions or blocks in order to have
    // one #content element to output.
    if (!count($render_array)) {
      $render_array = $this->buildSubRegions($region, $provider);
    }

    return array(
      '#theme' => $region->getPluginDefinition()['theme'],
      '#content' => $render_array,
      '#region' => $region,
      '#region_uuid' => $region->id(),
      '#region_id' => $region->getConfiguration()['region_id'],
    );
  }

  /**
   * Builds the blocks in a layout region.
   *
   * @param \Drupal\page_layout\Plugin\LayoutRegion\LayoutRegionInterface $region
   *   The layout region to render.
   * @param \Drupal\page_layout\LayoutBlockAndContextProviderInterface $provider
   *   The block and context provider needed to build the layout region.
   *
   * @return array
   *   The render array.
   */
  public function buildRegionBlocks(LayoutRegionInterface $region, LayoutBlockAndContextProviderInterface $provider) {
    $contexts = $provider->getContexts();
    // @todo: $region->id() is the UUID, and getBlocksByRegion() expects the region_id!
    //$blocksInRegion = $provider->getBlocksByRegion($region->id());
    $blocksInRegion = $provider->getBlocksByRegion($region->region_id());
    /** @var $blocksInRegion \Drupal\block\BlockPluginInterface[] */
    $render_array = array();
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
        $block_render_array = array(
          '#theme' => 'block',
          '#attributes' => array(),
          //'#weight' => $entity->get('weight'),
          '#configuration' => $block->getConfiguration(),
          '#plugin_id' => $block->getPluginId(),
          '#base_plugin_id' => $block->getBaseId(),
          '#derivative_plugin_id' => $block->getDerivativeId(),
        );
        $block_render_array['#configuration']['label'] = String::checkPlain($block_render_array['#configuration']['label']);
        $block_render_array['content'] = $block->build();

        $render_array[] = $block_render_array;
      }
    }
    return $render_array;
  }

  /**
   * Builds the subregions in the layout region.
   *
   * @param \Drupal\page_layout\Plugin\LayoutRegion\LayoutRegionInterface $region
   *   The layout to render.
   * @param \Drupal\page_layout\LayoutBlockAndContextProviderInterface $provider
   *   The block and context provider needed to build the layout region.
   *
   * @return array
   *   The render array.
   */
  public function buildSubRegions(LayoutRegionInterface $region, LayoutBlockAndContextProviderInterface $provider) {
    $regions = $this->getSubRegions($region, $provider);
    $subregions_render_array = array();
    if (count($regions)) {
      foreach ($regions as $region) {
        $subregions_render_array[] = $this->buildRegion($region, $provider);
      }
    }
    return $subregions_render_array;
  }

  /**
   * Retrieves subregions of a given layout region.
   *
   * @param \Drupal\page_layout\Plugin\LayoutRegion\LayoutRegionInterface $parent_region
   *   The layout region
   * @param \Drupal\page_layout\\LayoutBlockAndContextProviderInterface $provider
   *   The block and context provider needed to build the layout region.
   *
   * @return \Drupal\page_layout\Plugin\LayoutRegion\LayoutRegionInterface[]
   *   Instances of region plugins that are subregions.
   */
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
