<?php

/**
 * @file
 * Contains \Drupal\page_layout\NodeTypeFormController.
 */

namespace Drupal\page_layout\Controller;

use Drupal\page_manager\PageInterface;
use Drupal\page_manager\Plugin\PageVariantInterface;
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
class LayoutPageVariantRestController extends ContainerAware {

  public function handlePut(PageInterface $page, PageVariantInterface $page_variant, $data = array()) {

    foreach ($data['regions'] as $region) {
      foreach ($region['blocks'] as $block_data) {
        $block = $page_variant->getBlock($block_data['id']);
        if ($block) {
          $configuration = $block->getConfiguration();
          $configuration['region'] = $region['id'];
          $configuration['weight'] = $block_data['weight'];
          $block->setConfiguration($configuration);
        }
      }
    }
    $page->save();
    return new Response('{}', 200, array('Content-Type' => 'application/json'));
  }

  /**
   * {@inheritdoc}
   */
  public function handle(Request $request, PageInterface $page = NULL, $page_variant_id = NULL) {
    // @todo: this is clearly not restful - but let's get this on the road
    switch ($request->getMethod()) {
      case 'PUT':
        $page_variant = $page->getPageVariant($page_variant_id);
        if (!$page_variant) {
          throw BadRequestHttpException('Invalid page variant id provided');
        }
        $payload = $request->getContent();
        $data = json_decode($payload, TRUE);
        return $this->handlePut($page, $page_variant, $data);
        break;
      default:
        throw new BadRequestHttpException('Invalid HTTP method, currently only PUT supported');
    }
  }
}
