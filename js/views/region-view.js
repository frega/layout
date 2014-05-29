/**
 * @file
 * This view controls a region and the blocks contained in it. Opens
 * the BlockSelectorModalView on request.
 */
(function ($, _, Backbone, Drupal) {

  "use strict";

  Drupal.layout = Drupal.layout || {};

  Drupal.layout.RegionView = Backbone.View.extend({
    events:{
      'reorder':'reorderInstances'
    },
    _blockCollectionView: null,
    _subRegionCollectionView: null,
    // @todo: listen to collection events in app-view.js instead/propagate event.
    saveFullLayout: function(droppedModel) {
      // If we have a dropped model, let's make sure we update the region
      if (droppedModel) {
        droppedModel.set('region', this.model.get('id'));
      }

      // Show the "changed" notice.
      $('.display-changed').removeClass('js-hide');
      Drupal.layout.appModel.save();
    },
    initialize:function () {
      this.subregions = Drupal.layout.getRegionModelsByParentId(this.model.get('id'));
      if (this.subregions.length) {
        this._subRegionCollectionView = new Drupal.layout.UpdatingCollectionView({
          collection: new Drupal.layout.RegionCollection(this.subregions),
          nestedViewConstructor:Drupal.layout.RegionView,
          nestedViewTagName:'div',
          el: this.$el,
          nestedViewContainerSelector: '.regions .row'
        });
      }
      var blocks = this.model.get('blocks');
      this._blockCollectionView = new Drupal.layout.UpdatingCollectionView({
        collection: blocks,
        nestedViewConstructor:Drupal.layout.BlockInstanceView,
        nestedViewTagName:'div',
        el: this.$el,
        nestedViewContainerSelector: '.blocks > .row'
      });
      // @todo: be more selective about what changes trigger requests to the
      // server. And let that bubble up to the app-view or only persist the
      // region-specific changes here.
      blocks.on('reorder', this.saveFullLayout, this);
      blocks.on('add', this.saveFullLayout, this);
      blocks.on('remove', this.saveFullLayout, this);
    },

    render:function () {
      Drupal.layout.deajaxify(this.$el);

      this.$el.html(Drupal.theme.layoutRegion(this.model.get('id'), this.model.get('label'), this.model.toJSON()));

      // Render blocks
      this._blockCollectionView.render();
      // Making the whole layout-region-element sortable provides a larger area
      // to drop block instances on and allows for dropping on empty regions.
      this.$('.layout-region').sortable({
        items: '.blocks .block',
        connectWith: '.layout-region',
        cursor: 'move',
        placeholder: "ui-state-highlight",
        over: function(event, ui) {
          console.log(event, ui);
        },
        stop: function(event, ui) {
          console.log(ui.item.html(), ui.item.index());
          ui.item.trigger('drop', ui.item.index());
        }
      });

      if (this._subRegionCollectionView) {
        this._subRegionCollectionView.render();
      }

      Drupal.layout.ajaxify(this.$el);
      
      return this;
    },

    remove:function () {
      this.$el.sortable('destroy');
      this.$el.empty();
      this._blockCollectionView && this._blockCollectionView.remove();
      this._subRegionCollectionView && this._subRegionCollectionView.remove();

    },

    reorderInstances:function (event, model, position) {
      var collection = this.model.get('blocks');
      console.log(this.model.get('label'), collection.toJSON());
      // Handle cross-collection drag and drop.
      if (!collection.contains(model)) {
        var originCollection;
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
