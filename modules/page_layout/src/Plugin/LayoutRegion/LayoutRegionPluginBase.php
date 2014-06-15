<?php

/**
 * @file
 * Contains \Drupal\page_layout\Plugin\LayoutRegion\LayoutRegionPluginBase.
 */

namespace Drupal\page_layout\Plugin\LayoutRegion;

use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Component\Plugin\ContextAwarePluginInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\Context\ContextHandlerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\layout\Plugin\Layout\LayoutBlockAndContextProviderInterface;
use Drupal\page_layout\Plugin\LayoutPageVariantInterface;
use Drupal\layout\Plugin\LayoutRegion\LayoutConfigurableRegionBase;
use Symfony\Component\DependencyInjection\ContainerInterface;


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
   * {@inheritdoc}
   */
  public function getParentRegionId() {
    return isset($this->configuration['parent']) ? $this->configuration['parent'] : NULL;
  }

  public function getParentRegionOptions() {
    $regions = $this->provider->getLayoutRegions();
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

  public function getAllContainedRegionIds(LayoutPageVariantInterface $provider = NULL) {
    $provider = isset($provider) ? $provider : $this->provider;
    $regions = $this->getSubRegions($provider);
    $contained = array();
    if (sizeof($regions)) {
      foreach ($regions as $region) {
        $contained = array_merge($contained, array($region->id()), $region->getAllContainedRegionIds($provider));
      }
    }
    return $contained;
  }


  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, array &$form_state) {
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
  public function submitConfigurationForm(array &$form, array &$form_state) {
    $this->configuration['label'] = $form_state['values']['label'];
    $this->configuration['parent'] = isset($form_state['values']['region_positioning']['parent']) ?  $form_state['values']['region_positioning']['parent'] : NULL;
    $this->configuration['weight'] = isset($form_state['values']['region_positioning']['weight']) ?  $form_state['values']['region_positioning']['weight'] : 0;
  }

  public function calculateDependencies() {
    // @todo
  }
}
