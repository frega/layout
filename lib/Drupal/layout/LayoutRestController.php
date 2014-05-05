<?php

/**
 * @file
 * Contains \Drupal\layout\NodeTypeFormController.
 */

namespace Drupal\layout;

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

  public function put(Request $request) {
    $payload = $request->getContent();
    $data = json_decode($payload, TRUE);

    $layout = layout_load($data['id']);
    if (!$layout) {
      throw new BadRequestHttpException('Invalid layout id provided');
    }

    foreach ($data['containers'] as $region) {
      foreach ($region['components'] as $component_data) {
        $component = layout_component_load($component_data['id']);
        if ($component) {
          $component->set('container', $region['id']);
          $component->set('weight', $component_data['weight']);
          $component->save();
        }
      }
    }
    return new Response('{}', 200, array('Content-Type' => 'application/json'));
  }

  public function get(Request $request) {
    // Deserialze incoming data if available.
    $serializer = $this->container->get('serializer');

  }

  /**
   * {@inheritdoc}
   */
  public function handle(Request $request) {
    // @todo: this is clearly not restful - but let's get this on the road
    switch ($request->getMethod()) {
      case 'GET':
        return $this->get($request);
        break;
      case 'PUT':
        return $this->put($request);
        break;
    }
  }
}
