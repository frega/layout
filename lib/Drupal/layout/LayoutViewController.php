<?php
/**
 * @file
 * Contains \Drupal\layout\LayoutViewController.
 */
namespace Drupal\layout;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Defines dynamic routes.
 */
class LayoutViewController {
  /**
   * {@inheritdoc}
   */
  public function view(Request $request) {
    $layout_id = $request->get('_layout');
    $layout = layout_load($layout_id);
    if (!$layout) {
      return new BadRequestHttpException();
    }
    $render_array = $layout->build();
    return drupal_render($render_array);
  }
}
