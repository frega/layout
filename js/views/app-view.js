/**
 * @file
 * This file holds the master view for the layout js app.
 */
(function ($, _, Backbone, Drupal) {

  "use strict";

  Drupal.layout = Drupal.layout || {};
  Drupal.layout.AppView = Backbone.View.extend({
    initializeRegions: function() {
      this.regionsView = new Drupal.layout.UpdatingCollectionView({
        el: this.$el,
        collection: this.model.get('containers'),
        nestedViewConstructor:Drupal.layout.ContainerView,
        nestedViewTagName:'div'
      });
    },
    initialize: function(options) {
      this.initializeRegions();
      this.options = options;
      // Listen to changes of the layout-property for a complete repaint.
      this.model.on('change:layout', function(m) {
        this.remove();
        // Reinitialize region - @todo: find a way of doing this w/o
        // reinitializing the view.
        this.initializeRegions();
        this.render();
      }, this);
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
