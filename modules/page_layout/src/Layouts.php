<?php

namespace Drupal\page_layout;


use Drupal\block\BlockPluginInterface;
use Drupal\page_manager\PageInterface;
use Drupal\page_manager\Plugin\PageVariantInterface;

class Layouts {
  /**
   * Returns the plugin manager for a certain layout plugin type.
   *
   * @param string $type
   *   The plugin type, for example filter.
   *
   * @return \Drupal\page_layout\Plugin\LayoutPluginManager
   */
  public static function pluginManager($type) {
    return \Drupal::service('plugin.manager.layout.' . $type);
  }

  /**
   * Return all available templates as an options array.
   *
   * @return array
   */
  public static function getLayoutTemplateOptions() {
    $layoutTemplateManager = \Drupal::service('plugin.manager.layout.layout_template');
    // Sort the plugins first by category, then by label.
    $plugins = $layoutTemplateManager->getDefinitions();
    $options = array();
    foreach ($plugins as $id => $plugin) {
      $category = isset($plugin['category']) ? $plugin['category'] : 'default';
      if (!isset($options[$category])) {
        $options[$category] = array();
      }
      $options[$category][$id] = $plugin['title'];
    }
    return $options;
  }


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

  /**
   * @param PageVariantInterface $page_variant
   * @return array
   */
  public static function getGroupedBlockArrays(PageVariantInterface $page_variant) {
    $grouped = $page_variant->getRegionAssignments();
    $regions = $page_variant->getLayoutRegions();
    $data = array(
      'id' => $page_variant->id(),
      'layout' => $page_variant->getLayoutTemplateId(),
    );

    foreach ($regions as $region_id => $region) {
      $plugin_definition = $region->getPluginDefinition();
      $region_data = array(
        'id' => $region_id,
        'label' => $region->label(),
        'parent' => $region->getParentRegionId(),
        'plugin_id' => $plugin_definition['id'],
        'blocks' => array(),
      );

      $blocks = isset($grouped[$region_id]) && is_array($grouped[$region_id]) ? $grouped[$region_id] : array();
      foreach ($blocks as $block_id => $block) {
        $block_info = Layouts::blockToArray($block);
        $region_data['blocks'][] = $block_info;
      }
      $data['regions'][] = $region_data;
    }
    return $data;
  }


  public static function getLayoutPageVariantClientData(PageInterface $page, PageVariantInterface $page_variant) {
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
