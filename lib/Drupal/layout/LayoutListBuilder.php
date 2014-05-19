<?php

/**
 * @file
 * Contains \Drupal\node\NodeTypeListBuilder.
 */

namespace Drupal\layout;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityTypeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Component\Utility\Xss;

/**
 * Defines a class to build a listing of node type entities.
 *
 * @see \Drupal\layout\Entity\Layout
 */
class LayoutListBuilder extends ConfigEntityListBuilder {

  /**
   * The url generator service.
   *
   * @var \Drupal\Core\Routing\UrlGeneratorInterface
   */
  protected $urlGenerator;

  /**
   * Constructs a NodeTypeFormController object.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The entity storage class.
   * @param \Drupal\Core\Routing\UrlGeneratorInterface $url_generator
   *   The url generator service.
   */
  public function __construct(EntityTypeInterface $entity_type, EntityStorageInterface $storage, UrlGeneratorInterface $url_generator) {
    parent::__construct($entity_type, $storage);
    $this->urlGenerator = $url_generator;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('entity.manager')->getStorage($entity_type->id()),
      $container->get('url_generator')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = t('Label');
    $header['description'] = array(
      'data' => t('Description'),
      'class' => array(RESPONSIVE_PRIORITY_MEDIUM),
    );
    $header['path'] = array(
      'data' => t('Path'),
      'class' => array(RESPONSIVE_PRIORITY_MEDIUM),
    );
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['label'] = array(
      'data' => $this->getLabel($entity),
      'class' => array('menu-label'),
    );
    $row['description'] = Xss::filterAdmin($entity->description);
    $row['path'] = l($entity->get('path'), $entity->get('path'));
    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function getOperations(EntityInterface $entity) {
    $operations = parent::getOperations($entity);
    if ($entity->access('update') && $entity->hasLinkTemplate('configure-form')) {
      $operations['configure_containers'] = array(
          'title' => $this->t('Configure regions/containers'),
          'weight' => 14,
        ) + $entity->urlInfo('configure-containers-form')->toArray();

      $operations['configure'] = array(
          'title' => $this->t('Configure components'),
          'weight' => 15,
        ) + $entity->urlInfo('configure-form')->toArray();
    }

    return $operations;
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $build = parent::render();
    $build['#empty'] = t('No layouts available. <a href="@link">Add layout</a>.', array(
      '@link' => $this->urlGenerator->generateFromPath('admin/structure/layouts/add'),
    ));
    return $build;
  }

}
