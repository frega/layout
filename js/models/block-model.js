/**
 * @file
 * This model corresponds to the instance of a block placed in a region of a
 * layout.
 */
(function ($, _, Backbone, Drupal, drupalSettings) {
  "use strict";

  Drupal.layout = Drupal.layout || {};

  Drupal.layout.BlockModel = Backbone.Model.extend({
    url: function() {
      return drupalSettings.layout.webserviceURL + '/block';
    },
    defaults: {
      // Unique id of the block instance.
      'id': null,
      'weight': null,
      // Unique id of the block (e.g CMI key).
      'layout': '',
      'region': '',
      'config': {}
    }
  });

})(jQuery, _, Backbone, Drupal, drupalSettings);
