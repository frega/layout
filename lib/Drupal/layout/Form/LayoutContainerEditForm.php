<?php

/**
 * @file
 * Contains \Drupal\block_page\Form\PageVariantAddForm.
 */

namespace Drupal\layout\Form;

use Drupal\layout\Plugin\LayoutPluginManager;
use Drupal\layout\Form\LayoutContainerFormBase;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for adding a new page variant.
 */
class LayoutContainerEditForm extends LayoutContainerFormBase {

  /**
   * The page variant manager.
   *
   * @var \Drupal\block_page\Plugin\LayoutPluginManager
   */
  protected $layoutPluginManager;

  /**
   * Constructs a new PageVariantAddForm.
   *
   * @param \Drupal\layout\Plugin\LayoutPluginManager $layout_plugin_manager
   *   The layout manager.
   */
  public function __construct(LayoutPluginManager $layout_plugin_manager) {
    $this->layoutPluginManager = $layout_plugin_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.layout.layout_container')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'layout_layout_container_edit_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function submitText() {
    return $this->t('Edit layout container');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, array &$form_state) {
    parent::submitForm($form, $form_state);

    // Save the layout page.
    $this->layout->save();
    drupal_set_message($this->t('The %label container been updated.', array('%label' => $this->layoutContainer->label())));
    $form_state['redirect_route'] = new Url('layout.configure_containers', array(
      'layout' => $this->layout->id()
    ));
  }

  /**
   * {@inheritdoc}
   */
  protected function prepareLayoutContainer($container_id = 'default') {
    // Load the page variant directly from the block page.
    return $this->layout->getLayoutContainer($container_id);
  }

}
