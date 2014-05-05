/**
 * @file
 * This file contains the collections of models for the layout js-app.
 *
 * @todo: split into separate files.
 */
(function ($, _, Backbone, Drupal, drupalSettings) {
  "use strict";

  Drupal.layout = Drupal.layout || {};

  Drupal.layout.ContainerCollection = Backbone.Collection.extend({
    model: Drupal.layout.ContainerModel
  });

  Drupal.layout.ComponentCollection = Backbone.Collection.extend({
    model: Drupal.layout.ComponentModel,
    initialize: function() {
      // Reorder every time a component instance is added or removed.
      this.on('add', this.reorder, this);
      this.on('remove', this.reorder, this);
    },
    /**
     * Sorting callback for the collection.
     * @param {Drupal.layout.ComponentModel}
     * @return {Number}
     */
    comparator: function(model) {
      return model.get('weight');
    },
    /**
     * Make sure that weight attribute of the models correspond to their index.
     */
    reorder: function(options) {
      this.each(function (model, index) {
        model.set('weight', index);
      });
      if (!options || !options.silent) {
        this.trigger('reorder');
      }
    }
  });

})(jQuery, _, Backbone, Drupal, drupalSettings);
