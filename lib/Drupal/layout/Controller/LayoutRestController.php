<?php

/**
 * @file
 * Contains \Drupal\layout\NodeTypeFormController.
 */

namespace Drupal\layout\Controller ;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;


/**
 * Form controller for layout "restish".
 *
 * @todo: properly use REST/serializer/HAL. That will take care of CSRF and all the shebang ...
 *
 */
class LayoutRestController extends ContainerAware {

  public function put(Request $request, $layout = NULL) {
    $payload = $request->getContent();
    $data = json_decode($payload, TRUE);

    $layout = layout_load($data['id']);
    if (!$layout) {
      throw new BadRequestHttpException('Invalid layout id provided');
    }

    $layout->getLayoutContainers();

    foreach ($data['containers'] as $region) {
      foreach ($region['components'] as $component_data) {
        $block = $layout->getBlock($component_data['id']);
        if ($block) {
          $configuration = $block->getConfiguration();
          $configuration['region'] = $region['id'];
          $configuration['weight'] = $component_data['weight'];
          $block->setConfiguration($configuration);
        }
      }
    }
    $layout->save();

    return new Response('{}', 200, array('Content-Type' => 'application/json'));
  }

  /**
   * {@inheritdoc}
   */
  public function handle(Request $request) {
    // @todo: this is clearly not restful - but let's get this on the road
    switch ($request->getMethod()) {
      case 'PUT':
        return $this->put($request);
        break;
      default:
        throw new BadRequestHttpException('Invalid acces method, currently only PUT supported');
    }
  }
}
