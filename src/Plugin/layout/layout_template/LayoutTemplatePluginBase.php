<?php

namespace Drupal\layout\Plugin\layout\layout_template;

use Drupal\layout\LayoutStorageInterface;

use Drupal\layout\Plugin\LayoutPageVariantInterface;
use Drupal\page_manager\Plugin\PageVariantInterface;

use Drupal\layout\Plugin\LayoutRegionPluginBag;
use Drupal\layout\Plugin\LayoutPluginBase;
use Drupal\layout\Plugin\LayoutTemplatePluginInterface;

/**
 * The plugin that handles the default layout template.
 *
 * @ingroup layout_template_plugins
 *
 * @LayoutTemplate(
 *   id = "default",
 *   title = @Translation("Default template"),
 *   help = @Translation("Default layout template."),
 *   contextual_links_locations = {"page"},
 *   theme = "layout_template",
 *   admin = @Translation("Layout template"),
 *   derivative = "\Drupal\layout\Plugin\layout\layout_template\Derivative\DerivativeLayoutTemplate"
 * )
 */
class LayoutTemplatePluginBase extends LayoutPluginBase implements LayoutTemplatePluginInterface {
  var $configuration = array();

  public function getLayoutRegionPluginDefinitions() {
    return isset($this->pluginDefinition['regions']) ? $this->pluginDefinition['regions'] : array();
  }

  public function getRegionNames() {
    $regions = $this->getLayoutRegionPluginDefinitions();
    $names = array();
    foreach ($regions as $info) {
       $names[$info['id']] = $info['label'];
    }
    return $names;
  }

  public function build(LayoutPageVariantInterface $page_variant, $options = array()) {
    $regions = $page_variant->getLayoutRegions();
    $renderArray = array();
    $rootRegions = array();
    // Find rootRegions - @note we are doing it this way because *nesting* getLayoutRegions-calls
    // resets the internal iterator apparently.
    foreach ($regions as $region) {
      if (!$region->getParentRegionId()) {
        $rootRegions[] = $region;
      }
    }

    foreach ($rootRegions as $region) {
      $renderArray[] = $region->build($page_variant, $options);
    }

    return array(
      '#theme' => 'layout',
      '#regions' => $renderArray
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, array &$form_state) {
    $form['regions'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Regions'),
      '#description' => $this->t('The label for this region'),
      '#default_value' => $this->label(),
      '#maxlength' => '255',
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, array &$form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, array &$form_state) {
    $this->configuration['regions'] = $form_state['values']['regions'];
  }

  public function calculateDependencies() {
    // @todo
  }
}
