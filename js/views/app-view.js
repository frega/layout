/**
 * @file
 * This file holds the master view for the layout js app.
 */
(function ($, _, Backbone, Drupal) {

  "use strict";

  Drupal.layout = Drupal.layout || {};
  Drupal.layout.AppView = Backbone.View.extend({
    initializeRegions: function() {
      var rootRegions = new Drupal.layout.RegionCollection(this.model.get('regions').filter(function(region) {
        return (region.get('parent') === null || region.get('parent') === '');
      }));
      this.regionsView = new Drupal.layout.UpdatingCollectionView({
        el: this.$el,
        collection: rootRegions,
        nestedViewConstructor: Drupal.layout.RegionView,
        nestedViewTagName: 'div'
      });

      this.model.get('regions').on('change', function() {
        this.render();
      }, this);
    },
    initialize: function(options) {
      this.initializeRegions();
      this.options = options;
    },
    render: function() {
      this.regionsView.render();
      return this;
    },
    // @todo: we need to make this better.
    repaint: function() {
      this.initializeRegions();
      this.render();
    },
    remove: function() {
      this.regionsView.remove();
    }
  });

})(jQuery, _, Backbone, Drupal);
