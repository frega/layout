<?php

/**
 * @file
 * Contains \Drupal\page_layout\Plugin\LayoutRegion\LayoutRegionPluginBase.
 */

namespace Drupal\page_layout\Plugin\LayoutRegion;

use Drupal\Core\Form\FormStateInterface;
use Drupal\page_layout\Plugin\LayoutPageVariantInterface;
use Drupal\page_layout\Plugin\LayoutRegion\LayoutConfigurableRegionBase;

/**
 * The plugin that handles a default region
 *
 * @ingroup layout_region_plugins
 *
 * @LayoutRegion(
 *   id = "default",
 *   label = @Translation("Default region"),
 *   help = @Translation("Handles default layout region within a layout."),
 *   contextual_links_locations = {"page"},
 *   theme = "layout_region",
 *   admin = @Translation("Container")
 * )
 */
class LayoutRegionPluginBase extends LayoutConfigurableRegionBase {
  /**
   *
   * @var \Drupal\page_layout\Plugin\LayoutPageVariantInterface $pageVariant
   */
  public $pageVariant = NULL;

  public function init(LayoutPageVariantInterface $page_variant) {
    $this->pageVariant = $page_variant;
  }

  public function getParentRegionId() {
    return isset($this->configuration['parent']) ? $this->configuration['parent'] : NULL;
  }

  public function getParentRegionOptions() {
    $regions = $this->pageVariant->getLayoutRegions();
    $options = array();
    $contained_region_ids = $this->getAllContainedRegionIds();
    foreach ($regions as $region) {
      // @todo: filter to avoid nesting bugs & filter for valid parent region types.
      if ($region->id() !== $this->id() && !in_array($region->id(), $contained_region_ids)) {
        $options[$region->id()] = $region->label();
      }
    }
    return $options;
  }

  public function getSubRegions(LayoutPageVariantInterface $page_variant) {
    $page_variant = isset($page_variant) ? $page_variant : $this->pageVariant;
    $regions = $page_variant->getLayoutRegions();
    $filtered = array();
    foreach ($regions as $region) {
      if ($region->getParentRegionId() === $this->id()) {
        $filtered[] = $region;
      }
    }
    return $filtered;
  }

  public function getAllContainedRegionIds(LayoutPageVariantInterface $page_variant = NULL) {
    $page_variant = isset($page_variant) ? $page_variant : $this->pageVariant;
    $regions = $this->getSubRegions($page_variant);
    $contained = array();
    if (sizeof($regions)) {
      foreach ($regions as $region) {
        $contained = array_merge($contained, array($region->id()), $region->getAllContainedRegionIds($page_variant));
      }
    }
    return $contained;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['label'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#description' => $this->t('The label for this region'),
      '#default_value' => $this->label(),
      '#maxlength' => '255',
    );

    $form['region_positioning'] = array(
      '#type' => 'details',
      '#title' => t('Region positioning (parent and weight)'),
      '#open' => FALSE
    );

    $options = $this->getParentRegionOptions();
    $form['region_positioning']['parent'] = array(
      '#type' => 'select',
      '#title' => $this->t('Parent region'),
      '#description' => $this->t('Region to nest this region in'),
      '#options' => array(NULL => $this->t('-- No parent region --')) + $this->getParentRegionOptions(),
      '#default_value' => $this->getParentRegionId(),
      '#maxlength' => '255',
    );

    $form['region_positioning']['weight'] = array(
      '#type' => 'weight',
      '#title' => $this->t('Weight'),
      '#description' => $this->t('Weight of this region'),
      '#default_value' => $this->getWeight(),
      '#maxlength' => '255',
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['label'] = $form_state->getValue('label');
    $this->configuration['parent'] = $form_state->getValue(array('region_positioning', 'parent'));
    $this->configuration['weight'] = $form_state->getValue(array('region_positioning', 'weight'));
  }

  public function calculateDependencies() {
    // @todo
  }

  // {{{ UI & structural options.
  public function getOptions() {
    // @note: it would be nice to be able to merge.
    return isset($this->configuration['options']) ? $this->configuration['options'] : array();
  }

  protected function getOption($option, $default_value = NULL) {
    $options = $this->getOptions();
    return isset($options[$option]) ? $options[$option] : $default_value;
  }

  public function canAddSubregions() {
    return $this->getOption('can_add_subregions');
  }

  public function canBeDeleted() {
    // @todo: check if this is a dynamically added subregion
    return $this->getOption('can_be_deleted', FALSE);
  }
}
