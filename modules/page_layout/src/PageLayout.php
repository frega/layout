<?php

namespace Drupal\page_layout;

use Drupal\page_layout\LayoutPageAction;
use Drupal\Core\Url;
use Drupal\block\BlockPluginInterface;
use Drupal\layout\Plugin\LayoutRegion\LayoutRegionInterface;
use Drupal\page_layout\Plugin\LayoutPageVariantInterface;
use Drupal\page_manager\PageInterface;

class PageLayout {
  /**
   * Converts a BlockPluginInterface to a minimal array (id, label, weight and region/region)
   *
   * @param BlockPluginInterface $block
   * @return array
   */
  public static function blockToArray(BlockPluginInterface $block) {
    $config = $block->getConfiguration();
    $settings = isset($config['settings']) ? $config['settings'] : array();
    $definition = $block->getPluginDefinition();
    if (isset($config['label']) && !empty($config['label'])) {
      $label = $config['label'];
    }
    else {
      $label = isset($definition['admin_label']) ? $definition['admin_label'] : $block->getPluginId();
    }
    return array(
      'id' => $config['uuid'],
      'label' => $label,
      'weight' => isset($config['weight']) ? $config['weight'] : 0,
      'region' => $config['region']
    );
  }

  public static function getRegionActions(LayoutPageVariantInterface $page_variant, LayoutRegionInterface $region) {
    $actions = array();
    $page = $page_variant->getPage();

    if ($region->canAddBlocks()) {
      $actions[] = new LayoutPageAction(t('Add block'),
        new Url('layout.page_variant_layout_blocks_select', array(
          'page' => $page->id(),
          'page_variant_id' => $page_variant->id(),
          'layout_region_id' => $region->id()
        ))
      );
    }

    if ($region->isConfigurable()) {
      $actions[] = new LayoutPageAction(t('Configure region'),
        new Url('layout.layout_region_edit', array(
          'page' => $page->id(),
          'page_variant_id' => $page_variant->id(),
          'layout_region_id' => $region->id()
        ))
      );
    }

    if ($region->canAddSubregions()) {
      $actions[] = new LayoutPageAction(t('Add subregion'),
        new Url('layout.page_variant_layout_regions_select', array(
          'page' => $page->id(),
          'page_variant_id' => $page_variant->id(),
          'layout_region_id' => $region->id()
        ))
      );
    }

    if ($region->canBeDeleted()) {
      $actions[] = new LayoutPageAction(t('Delete region'),
        new Url('layout.layout_region_delete', array(
          'page' => $page->id(),
          'page_variant_id' => $page_variant->id(),
          'layout_region_id' => $region->id()
        ))
      );
    }

    $action_array = array();
    foreach ($actions as $action) {
      /** @var $action \Drupal\page_layout\LayoutPageAction */
      $action_array[] = $action->toArray();
    }

    return $action_array;
  }

  /**
   * @param LayoutPageVariantInterface $page_variant
   * @return array
   */
  public static function getGroupedBlockArrays(LayoutPageVariantInterface $page_variant) {
    $grouped = $page_variant->getRegionAssignments();
    $regions = $page_variant->getLayoutRegions();
    $data = array(
      'id' => $page_variant->id(),
      'layout' => $page_variant->getLayoutId(),
    );

    foreach ($regions as $region_id => $region) {
      // $region->init($page_variant);
      $plugin_definition = $region->getPluginDefinition();
      $region_data = array(
        'id' => $region_id,
        'label' => is_object($region->label()) ? (string) $region->label() : $region->label(),
        'parent' => $region->getParentRegionId(),
        'plugin_id' => $plugin_definition['id'],
        'weight' => $region->getWeight(),
        'actions' => self::getRegionActions($page_variant, $region),
        'options' => $region->getOptions(),
        'blocks' => array(),
      );

      $blocks = isset($grouped[$region_id]) && is_array($grouped[$region_id]) ? $grouped[$region_id] : array();
      foreach ($blocks as $block_id => $block) {
        $block_info = PageLayout::blockToArray($block);
        $region_data['blocks'][] = $block_info;
      }
      $data['regions'][] = $region_data;
    }
    return $data;
  }


  public static function getLayoutPageVariantClientData(LayoutPageVariantInterface $page_variant) {
    $page = $page_variant->getPage();
    return array(
      'layout' => array(
        'id' => $page->id(),
        'pageId' => $page->id(),
        'variantId' => $page_variant->id(),
        'layoutData' => self::getGroupedBlockArrays($page_variant),
        'locked' => FALSE,
        'webserviceURL' => \Drupal::urlGenerator()->generateFromRoute('layout.page_variant_layout_rest', array(
            'page' => $page->id(),
            'page_variant_id' => $page_variant->id()
          ))
      )
    );
  }
}
