/**
 * @file
 * This model corresponds to the instance of a component placed in a region of a
 * layout.
 */
(function ($, _, Backbone, Drupal, drupalSettings) {
  "use strict";

  Drupal.layout = Drupal.layout || {};

  Drupal.layout.ComponentModel = Backbone.Model.extend({
    url: function() {
      return drupalSettings.layout.webserviceURL + '/component';
    },
    defaults: {
      // Unique id of the component instance.
      'id': null,
      'weight': null,
      // Unique id of the component (e.g CMI key).
      'layout': '',
      'container': '',
      'config': {}
    }
  });

})(jQuery, _, Backbone, Drupal, drupalSettings);
