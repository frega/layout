/**
 * @file
 * This view controls a single ComponentInstance.
 */
(function ($, _, Backbone, Drupal) {

  "use strict";

  Drupal.layout = Drupal.layout || {};

  Drupal.layout.ComponentInstanceView = Backbone.View.extend({
    events:{
      'drop':'onDrop'
    },
    initialize: function() {
      this.model.on('change', this.render, this);
    },
    onDrop:function (event, index) {
      // Trigger reorder, will be handled in Drupal.layout.ContainerView.
      this.$el.trigger('reorder', [this.model, index]);
      event.preventDefault();
      event.stopPropagation();
      return ;
    },
    render:function () {
      // Remove any existent Drupal.ajax.
      Drupal.layout.deajaxify(this.$el);
      // If you want to have the template render the "top" element of your view
      // you need to do this.
      // @see http://stackoverflow.com/questions/11594961/backbone-not-this-el-wrapping
      var old = this.$el;
      this.setElement(Drupal.theme('layoutComponent', this.model.get('id'), this.model.get('label'), {
        'configurePath': '/admin/structure/layout/manage/' + drupalSettings.layout.id + '/components/' + this.model.get('container') + '/' + this.model.get('id') + '/edit',
        'deletePath': '/admin/structure/layout/manage/' + drupalSettings.layout.id + '/components/' + this.model.get('container') + '/' + this.model.get('id') + '/delete'
      }));
      old.replaceWith(this.$el);
      // Rewire Drupal.ajax.
      Drupal.layout.ajaxify(this.$el);
      return this;
    },
    remove: function() {
      Drupal.layout.deajaxify(this.$el);
      this.$el.remove();
    }
  });


})(jQuery, _, Backbone, Drupal);
