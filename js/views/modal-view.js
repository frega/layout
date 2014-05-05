/**
 * @file
 * Base view wrapping a dialog.js based dialog.
 *
 * @todo: maybe provide meaningful form-loading?
 */
(function ($, _, Backbone, Drupal) {

  "use strict";

  Drupal.layout = Drupal.layout || {};

  Drupal.layout.ModalView = Backbone.View.extend({
    dialog: null,
    callback: null,
    initialize: function(options) {
      this.options = options;
      this.callback = options.callback || null;
      this.dialog = Drupal.dialog(this.$el, {title: options.title});
    },
    show: function() {
      this.dialog.showModal();
    },
    close: function() {
      this.dialog.close();
    },
    remove: function() {
      // Apparently no need to call this.dialog.close(); remove this.$el
      // closes the jQueryUI Dialog, oh jqueryui magic ...
      this.$el.remove();
    }
  });

})(jQuery, _, Backbone, Drupal);
