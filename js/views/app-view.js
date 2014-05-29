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
    },
    initialize: function(options) {
      this.initializeRegions();
      this.options = options;
    },
    render: function() {
      // @todo: this should move to layout.admin.js and provide better handling.
      // Do not setup the js app if another user is currently operating on this
      // layout (locked on the server via TempStore).
      if (this.options.locked) {
        return false;
      }

      this.regionsView.render();
      return this;
    },
    remove: function() {
      this.regionsView.remove();
    }
  });

})(jQuery, _, Backbone, Drupal);
