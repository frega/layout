<?php

/**
 * @file
 * Contains \Drupal\layout\Form\LayoutDeleteConfirmForm.
 */

namespace Drupal\layout\Form;

use Drupal\Core\Entity\EntityConfirmFormBase;
use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for content type deletion.
 */
class LayoutComponentDeleteConfirmForm extends EntityConfirmFormBase {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Constructs a new NodeTypeDeleteConfirm object.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   */
  public function __construct(Connection $database) {
    $this->database = $database;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return t('Are you sure you want to delete the layout component %name?', array('%name' => $this->entity->label()));
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelRoute() {
    return array(
      'route_name' => 'layout.configure',
      'route_parameters' => array(
        'layout' => $this->entity->get('layout')
      )
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, array &$form_state) {
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submit(array $form, array &$form_state) {
    $this->entity->delete();
    $t_args = array('%name' => $this->entity->label());
    drupal_set_message(t('The layout component %name has been deleted.', $t_args));
    watchdog('layout_component', 'Deleted layout component %name.', $t_args, WATCHDOG_NOTICE);

    $form_state['redirect_route'] = array(
      'route_name' => 'layout.configure',
      'route_parameters' => array(
        'layout' => $this->entity->get('layout')
      )
    );
  }

}
