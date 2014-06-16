<?php
namespace Drupal\page_layout;


class LayoutPageAction {
  var $id;
  var $label;
  var $url;
  var $options;

  function __construct($id, $label, $url, $options = array()) {
    $this->id = $id;
    $this->label = $label;
    $this->url = $url;
    $this->options = $options;
  }

  function id() {
    return $this->id;
  }

  function toArray() {
    return array(
      'label' => $this->label,
      'url' => is_object($this->url) ? $this->url->toString() : $this->url,
      'options' => $this->options
    );
  }

}
