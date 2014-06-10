/**
 * @file
 */
(function ($, _, Backbone, Drupal) {
  "use strict";

  Drupal.layout = Drupal.layout || {};

  Drupal.layout.UpdatingCollectionView = Backbone.View.extend({
    initialize:function (options) {
      if (!options.nestedViewConstructor) {
        throw new Error("no child view constructor provided");
      }
      if (!options.nestedViewTagName) {
        throw new Error("no child view tag name provided");
      }

      this.options = options;
      this._nestedViews = [];
      this.collection.forEach(this._addModel, this);
      this.collection.bind('add', this._addModel, this);
      this.collection.bind('remove', this._removeModel, this);
    },

    /**
     * Retrieves a nested Backbone.View by its Backbone.Model
     * @param model
     * @return {Backbone.Model}
     * @private
     */
    _getViewByModel: function(model) {
      // @todo this probably should be cached/tracked.
      var vs = _(this._nestedViews).select(function (nv) {
        return nv.model === model;
      });
      return vs.length ? vs[0] : false;
    },

    /**
     * Return either this.$el or if a nestedViewContainerSelector-option was
     * given the element that matches this.$(nestedViewContainerSelector).
     *
     * @return {jQuery}
     * @private
     */
    _getContainerElement: function() {
      if (this.options.nestedViewContainerSelector) {
        return this.$(this.options.nestedViewContainerSelector);
      }
      else {
        return this.$el;
      }
    },

    /**
     * Called when a new model is added to the collection.
     *
     * @param {Backbone.Model} model
     * @private
     */
    _addModel:function (model) {
      var nv = new this.options.nestedViewConstructor({
        tagName:this.options.nestedViewTagName,
        model:model
      });

      this._nestedViews.push(nv);
      if (this._rendered) {
        this._getContainerElement().append(nv.render().$el);
      }
    },

    /**
     * Called when a new model is removed from the collection.
     *
     * @param {Backbone.Model} model
     * @private
     */
    _removeModel:function (model) {
      var viewToRemove = this._getViewByModel(model);
      this._nestedViews = _(this._nestedViews).without(viewToRemove);
      if (this._rendered && viewToRemove) {
        viewToRemove.remove();
      }
    },

    /**
     * Renders all nested views (one per model in the view's collection).
     *
     * @return {Drupal.layout.UpdatingCollectionView}
     */
    render:function () {
      this._rendered = true;
      var $el  = this._getContainerElement();
      $el.empty();
      // Use the collection to make sure the order of the rendered views is
      // up-to-date.
      this.collection.each(function(m) {
        var nv = this._getViewByModel(m);
        // Check that a view could be retrieved.
        if (nv) {
          $el.append(nv.render().$el);
        }
      }, this);
      return this;
    },

    /**
     * Remove all nested views.
     * @todo: should we instead remove the models from the collection? Currently
     * we leave the collection intact but retrieve each nested view and remove it.
     */
    remove: function() {
      // Cleanup.
      this.collection.each(function(m) {
        this._removeModel(m);
      }, this);
      this._getContainerElement().remove();
    }
  });

})(jQuery, _, Backbone, Drupal);
