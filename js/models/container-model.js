/**
 * @file
 * This model corresponds to a region in a layout.
 */
(function ($, _, Backbone, Drupal) {
  "use strict";

  Drupal.layout = Drupal.layout || {};

  Drupal.layout.ContainerModel = Backbone.Model.extend({
    url: function() {
      return drupalSettings.layout.webserviceURL + '/region/' + this.get('id');
    },
    defaults: {
      'id': null,
      // Drupal.layout.ComponentCollection.
      'components': null,
      'config': null
    }
  });

})(jQuery, _, Backbone, Drupal);
