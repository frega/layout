/**
 * @file
 * This model corresponds to a region in a layout.
 */
(function ($, _, Backbone, Drupal) {
  "use strict";

  Drupal.layout = Drupal.layout || {};

  Drupal.layout.RegionModel = Backbone.Model.extend({
    url: function() {
      return drupalSettings.layout.webserviceURL + '/region/' + this.get('id');
    },
    defaults: {
      'id': null,
      // Drupal.layout.BlockCollection.
      'blocks': null,
      'parent': null,
      'plugin_id': null,
      'plugin_type': null,
      'actions': {}
    }
  });

})(jQuery, _, Backbone, Drupal);
