/**
 * @file
 * This view controls a region and the components contained in it. Opens
 * the ComponentSelectorModalView on request.
 */
(function ($, _, Backbone, Drupal) {

  "use strict";

  Drupal.layout = Drupal.layout || {};

  Drupal.layout.ContainerView = Backbone.View.extend({
    events:{
      'click [name="component"][value="configure"]':'onClickConfigure',
      'reorder':'reorderInstances'
    },

    // @todo: listen to collection events in app-view.js instead/propagate event.
    saveFullLayout: function(droppedModel) {
      // If we have a dropped model, let's make sure we update the container
      if (droppedModel) {
        droppedModel.set('container', this.model.get('id'));
      }

      // Show the "changed" notice.
      $('.display-changed').removeClass('js-hide');
      Drupal.layout.appModel.save();
    },

    initialize:function () {
      var components = this.model.get('components');
      this._collectionView = new Drupal.layout.UpdatingCollectionView({
        collection:components,
        nestedViewConstructor:Drupal.layout.ComponentInstanceView,
        nestedViewTagName:'div',
        el: this.$el,
        nestedViewContainerSelector: '.components .row'
      });

      // @todo: be more selective about what changes trigger requests to the
      // server. And let that bubble up to the app-view or only persist the
      // region-specific changes here.
      components.on('reorder', this.saveFullLayout, this);
      components.on('add', this.saveFullLayout, this);
      components.on('remove', this.saveFullLayout, this);
    },

    render:function () {
      this.$el.html(Drupal.theme.layoutContainer(this.model.get('id'), this.model.get('label'), this.model.toJSON()));
      this._collectionView.render();
      // Making the whole layout-container-element sortable provides a larger area
      // to drop component instances on and allows for dropping on empty containers.
      this.$('.layout-container').sortable({
        items: '.component',
        connectWith: '.layout-container',
        cursor: 'move',
        stop: function(event, ui) {
          ui.item.trigger('drop', ui.item.index());
        }
      });
      Drupal.layout.ajaxify(this.$el);
      return this;
    },

    remove:function () {
      this.$el.sortable('destroy');
      this.$el.empty();
      this._collectionView.remove();
    },

    reorderInstances:function (event, model, position) {
      var collection = this.model.get('components');
      var originCollection;
      // Handle cross-collection drag and drop.
      if (!collection.contains(model)) {
        originCollection = model.collection;
        // Let's remove it from the other first before adding it here.
        model.collection.remove(model, {silent: true});
        // This is set to silent to avoid potential race condition.
        originCollection.reorder({silent: true});
      } else {
        // We'll be re-adding immediately, so no need for rapid-fire events.
        collection.remove(model, {silent: true});
      }
      collection.add(model, {at:position});
      this.render();
    }
  });

})(jQuery, _, Backbone, Drupal);
