/**
 * @file
 * This view controls a region and the blocks contained in it. Opens
 * the BlockSelectorModalView on request.
 */
(function ($, _, Backbone, Drupal) {

  "use strict";

  Drupal.layout = Drupal.layout || {};

  Drupal.layout.RegionView = Backbone.View.extend({
    _blockCollectionView: null,
    _subRegionCollectionView: null,
    initialize:function () {
      var subregions = Drupal.layout.getRegionModelsByParentId(this.model.get('id'));
      this._subRegionCollectionView = new Drupal.layout.UpdatingCollectionView({
        collection: new Drupal.layout.RegionCollection(subregions),
        nestedViewConstructor:Drupal.layout.RegionView,
        nestedViewTagName:'div',
        el: this.$el,
        nestedViewContainerSelector: '.regions .row'
      });

      var blocks = this.model.get('blocks');
      this._blockCollectionView = new Drupal.layout.UpdatingCollectionView({
        collection: blocks,
        nestedViewConstructor:Drupal.layout.BlockView,
        nestedViewTagName:'div',
        el: this.$el,
        // @note: as we have nested UpdatingCollectionView we must avoid it applying to any nested regions.
        nestedViewContainerSelector: '#layout-region-blocks-' + this.model.get('id') + ' .row'
      });


      // If the collection is reordered, let's persist the changes via pseudo-REST.
      blocks.on('reorder', function() {
        // @note: we currently don't have a "partial" save/patch method, we are
        // sending all regions and blocks at once.
        Drupal.layout.appModel.save();
      }, this);

      // If the parent of the region changes, let's repaint the whole app.
      this.model.on('change:parent', function() {
        Drupal.layout.appView.repaint();
      }, this);

      // If the weight of the region changes, let's repaint the whole app.
      this.model.on('change:weight', function() {
        Drupal.layout.appView.repaint();
      }, this);
    },

    render:function () {
      var self = this;
      Drupal.layout.deajaxify(this.$el);
      this.$el.empty();
      this.$el.html(Drupal.theme.layoutRegion(this.model.get('id'), this.model.get('label'), this.model.toJSON()));

      // Render blocks
      this._blockCollectionView.render();
      this.setupBlockSortable();

      // Render subregions.
      if (this._subRegionCollectionView) {
        this._subRegionCollectionView.render();
      }

      Drupal.layout.ajaxify(this.$el);
      return this;
    },

    setupBlockSortable: function() {
      var self = this;
      // Making the whole layout-region-element sortable provides a larger area
      // to drop block instances on and allows for dropping on empty regions.
      this.$('.layout-region .blocks').sortable({
        items: '.block',
        connectWith: '.layout-region .blocks',
        cursor: 'move',
        placeholder: 'block block-placeholder',
        start: function() {
          // We we need to do this because jQuery ui sortable makes it *hard* to distinguish between
          // cross- and intra-sortable drags
          this.status = null;
          // Events fired in case of cross-sortable drag'n'drop:
          // 1. 'update' on source
          // 2. 'remove' on source
          // 3. 'receive' on target
          // 4. 'update' on target
          // Events fired in case of intra-sortable drag'n'drop:
          // 1. 'update' on source/target
          // We handle a move between sortables in the 'receive' event
          // and make sure that only one update event is triggered.

          Drupal.layout.appView.$el.addClass('layout-block-dragging');
        },
        stop: function(event, ui) {
          if (this.status === 'updated') {
            var model = Drupal.layout.getBlockModelById( $(ui.item).data('uuid') );
            self.moveBlock(model, ui.item.index());
          }
          this.status = null;
          Drupal.layout.appView.$el.removeClass('layout-block-dragging');
        },
        update: function(event, ui) {
          // Only set status to 'updated', if the value hasn't been set already
          this.status = this.status ? this.status : 'updated';
        },
        remove: function(event,ui) {
          // Set the status to 'removed', skipping additional updates on stopping the drag.
          this.status = 'removed';
        },
        receive: function(event, ui) {
          // Set the status to 'received', skipping additional updates on stopping the drag.
          this.status = 'received';
          self.moveBlock(Drupal.layout.getBlockModelById($(ui.item).data('uuid')), ui.item.index());
        }
      });
    },

    remove: function () {
      // Destroy sortable.
      this.$('.layout-region .blocks').sortable('destroy');

      // Remove collection views.
      this._blockCollectionView && this._blockCollectionView.remove();
      this._subRegionCollectionView && this._subRegionCollectionView.remove();

      // Unbind events.
      this.model.off('change:parent');
      this.model.get('blocks').off('reorder');

      // Empty element.
      this.$el.empty();
    },

    moveBlock: function(model, position) {
      var oldCollection = model.collection;
      oldCollection.remove(model, {silent: true});

      model.set('region', this.model.get('id'));
      this.model.get('blocks').add(model, {at:position, silent: true});
      this.model.get('blocks').reorder();
    }
  });

})(jQuery, _, Backbone, Drupal);
