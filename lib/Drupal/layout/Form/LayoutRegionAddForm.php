<?php

/**
 * @file
 * Contains \Drupal\block_page\Form\PageVariantAddForm.
 */

namespace Drupal\layout\Form;

use Drupal\layout\Plugin\LayoutPluginManager;
use Drupal\layout\Form\LayoutRegionFormBase;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for adding a new page variant.
 */
class LayoutRegionAddForm extends LayoutRegionFormBase {

  /**
   * The page variant manager.
   *
   * @var \Drupal\block_page\Plugin\LayoutPluginManager
   */
  protected $layoutPluginManager;

  /**
   * Constructs a new PageVariantAddForm.
   *
   * @param \Drupal\block_page\Plugin\PageVariantManager $layout_plugin_manager
   *   The page variant manager.
   */
  public function __construct(LayoutPluginManager $layout_plugin_manager) {
    $this->layoutPluginManager = $layout_plugin_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.layout.layout_region')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'layout_layout_region_add_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function submitText() {
    return $this->t('Add layout region');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, array &$form_state) {
    parent::submitForm($form, $form_state);

    // If this page variant is new, add it to the page.
    $region_id = $this->layout->addLayoutRegion($this->layoutRegion->getConfiguration());

    // Save the layout page.
    $this->layout->save();
    drupal_set_message($this->t('The %label region been added.', array('%label' => $this->layoutRegion->label())));
    $form_state['redirect_route'] = new Url('layout.layout_regions', array(
      'layout' => $this->layout->id()
    ));
  }

  /**
   * {@inheritdoc}
   */
  protected function prepareLayoutRegion($region_id = 'default') {
    // Create a new page variant instance.
    return $this->layoutPluginManager->createInstance($region_id);
  }

}
