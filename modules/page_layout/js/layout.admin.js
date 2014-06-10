(function ($, window, Drupal, drupalSettings) {

"use strict";

Drupal.layout = Drupal.layout || {};

Drupal.AjaxCommands.prototype.layoutBlockReload = function (ajax, response, status) {
  // Find the appropriate model by its id.
  var m = Drupal.layout.getBlockModelById(response.data.id);
  if (m) {
    // The views will take care of all necessary updates (unbinding, rebinding).
    m.set(response.data);
  } else {
    // Retrieve the region model
    var c = Drupal.layout.getRegionModelById(response.data.region);
    if (c) {
      // Instantiate a new block model, the region will repaint etc.
      // @todo: fix weight/order will probably be *off*
      c.get('blocks').add(new Drupal.layout.BlockModel(response.data));
    }
  }
};

Drupal.AjaxCommands.prototype.layoutBlockDelete = function (ajax, response, status) {
  // Find the appropriate model by its id.
  var m = Drupal.layout.getBlockModelById(response.data.id);
  if (m) {
    // The views will take care of all necessary updates (unbinding, rebinding).
    m.destroy();
  }
}

Drupal.AjaxCommands.prototype.layoutReload = function (ajax, response, status) {
  // @note: we need to improve this: this is not efficient at all.
  Drupal.layout.appModel = new Drupal.layout.AppModel({
    id: response.data.layout.id,
    layout: response.data.layout.layoutData.layout,
    regions: Drupal.layout.generateRegionCollections(response.data.layout.layoutData)
  });

  Drupal.layout.appView = new Drupal.layout.AppView({
    model: Drupal.layout.appModel,
    el: $('#content .form-item-page-variant-blocks .form-textarea-wrapper'),
    locked: drupalSettings.layout.locked
  });

  Drupal.layout.appView.render();
};

Drupal.AjaxCommands.prototype.layoutRegionReload = function (ajax, response, status) {
  // Find the appropriate model by its id.
  var m = Drupal.layout.getRegionModelById(response.data.id);
  if (m) {
    // The views will take care of all necessary updates (unbinding, rebinding).
    var blocks = new Drupal.layout.BlockCollection();
    blocks.reset(response.data.blocks);
    response.data.blocks = blocks;
    m.set(response.data);
  } else {
    // Retrieve the region model
    var c = Drupal.layout.getRegionModelById(response.data.parent);

    return ;
  }
};

/**
 * Bind links to open in a dialog using Drupal.ajax.
 * @param el
 */

Drupal.layout.ajaxify = function(el) {
  // Bind Ajax behaviors to all items showing the class.
  $(el).find('.ajax').once('ajax', function () {
    var element_settings = {};
    element_settings.progress = { 'type': null };

    if ($(this).attr('href')) {
      element_settings.url = $(this).attr('href');
      element_settings.event = 'click.layout';
    }
    element_settings.accepts = $(this).data('accepts');
    element_settings.dialog = $(this).data('dialog-options');

    var base = $(this).attr('id');
    Drupal.ajax[base] = new Drupal.ajax(base, this, element_settings);
  });
}

/**
 * Unbind links bound to Drupal.ajax.
 * @param el
 */
Drupal.layout.deajaxify = function(el) {
  $(el).find('.ajax').each(function() {
    var base = $(this).attr('id');
    if (Drupal.ajax[base]) {
      $(this).off('click.layout');
      delete Drupal.ajax[base]
    }
  });
}

/**
 * Retrieve the BlockModel
 * @param id
 * @return {*}
 */
Drupal.layout.getBlockModelById = function(id) {
  var m;
  Drupal.layout.appModel.get('regions').each(function(region) {
    m = m || region.get('blocks').get(id);
  }, this);
  return m;
};

Drupal.layout.getRegionModelById = function(id) {
  return Drupal.layout.appModel.get('regions').get(id);
};

Drupal.layout.getRegionModelsByParentId = function(id) {
  return Drupal.layout.appModel.get('regions').where({parent: id});
};

/**
 * Generates the required Backbone Collections and Models.
 * @param layoutData
 * @return {Drupal.layout.RegionCollection}
 */
Drupal.layout.generateRegionCollections = function(layoutData) {
  var regions = new Drupal.layout.RegionCollection();
  _(layoutData.regions).each(function(region) {
    var blocks = new Drupal.layout.BlockCollection();
    blocks.reset(region.blocks);
    regions.add(new Drupal.layout.RegionModel({
      id: region.id,
      label: region.label,
      blocks: blocks,
      parent: region.parent,
      weight: region.weight,
      plugin_id: region.plugin_id
    }));
  });
  return regions;
};


/**
 * Attach display editor functionality.
 */
Drupal.behaviors.displayLayoutEditor = {
  attach: function (context, settings) {
    // Initial attaching.
    if (!Drupal.layout.appView) {
      Drupal.layout.appModel = new Drupal.layout.AppModel({
        id: drupalSettings.layout.id,
        layout: drupalSettings.layout.layoutData.layout,
        regions: Drupal.layout.generateRegionCollections(drupalSettings.layout.layoutData)
      });

      Drupal.layout.appView = new Drupal.layout.AppView({
        model: Drupal.layout.appModel,
        el: $('#content .form-item-page-variant-blocks .form-textarea-wrapper'),
        locked: drupalSettings.layout.locked
      });

      Drupal.layout.appView.render();
    }

  }
};

})(jQuery, window, Drupal, drupalSettings);
