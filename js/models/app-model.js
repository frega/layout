/**
 * @file
 * This model hold application state and corresponds to the layout containing
 * containers (and component instances).
 *
 * @todo: probably split this AppModel into AppModel and LayoutModel.
 */
(function ($, _, Backbone, Drupal) {
  "use strict";

  Drupal.layout = Drupal.layout || {};
  Drupal.layout.AppModel = Backbone.Model.extend({
    url: function() {
      return drupalSettings.layout.webserviceURL;
    },
    defaults: {
      'id': null,
      'layout': null,
      'containers': null,
      'config': null
    }
  });

})(jQuery, _, Backbone, Drupal);
